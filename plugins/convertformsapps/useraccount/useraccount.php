<?php

/**
 * @package         Convert Forms
 * @version         4.2.4 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2023 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use ConvertForms\Tasks\App;
use ConvertForms\Tasks\Helper;
use NRFramework\Functions;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
class plgConvertFormsAppsUserAccount extends App
{
	/**
	 * The trigger Subscribe
	 *
	 * @return void
	 */
	public function actionRegister()
	{
		// Sanity check. Is user registration allowed?
		if (ComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0)
		{
			$this->setError('User registration is disabled.');
			return false;
		}

        $data = $this->options;
        $data['groups'] = Functions::cleanArray(Helper::readRepeatSelect($data['groups']));

        return $this->register($data);
	}

    /**
     * Get a list with the fields needed to setup the app's event.
     *
     * @return array
     */
	public function getActionRegisterSetupFields()
	{
        Factory::getLanguage()->load('com_users');

        // Get Users component configuration options
        $userSettings = ComponentHelper::getParams('com_users');

		$globalUserActivation = (int) $userSettings->get('useractivation');

        switch ($globalUserActivation)
        {
            case 0:
                $globalUserActivation = 'JNONE';
                break;

            case 1:
                $globalUserActivation = 'COM_USERS_CONFIG_FIELD_USERACTIVATION_OPTION_SELFACTIVATION';
                break;

            case 2:
                $globalUserActivation = 'COM_USERS_CONFIG_FIELD_USERACTIVATION_OPTION_ADMINACTIVATION';
        }

		$globalNewUserGroup = (int) $userSettings->get('new_usertype');

        return [
            [
                'name' => Text::_('COM_CONVERTFORMS_APP_SETUP_ACTION'),
                'fields' => [
                    $this->field('email'),
                    $this->field('name'),
                    $this->field('username'),
                    $this->field('password', [
                        'value' => '{randomid}',
                        'options' => [
                            [
                                'label' => $this->lang('GENERATE_RANDOM_PASSWORD'),
                                'value' => '{randomid}'
                            ]
                        ]
                    ]),
                    [
                        'name'  => 'groups',
                        'type'  => 'repeat-select',
                        'value' => [
                            [
                                'value' => $globalNewUserGroup
                            ]
                        ],
                        'label' => $this->lang('GROUPS'),
                        'hint'  => $this->lang('GROUPS_DESC'),
                        'options' => $this->getGroups()
                    ],
                    [
                        'name'  => 'activation',
                        'value' => 'use_global',
                        'label' => Text::_('COM_USERS_CONFIG_FIELD_USERACTIVATION_LABEL'),
                        'hint'  => $this->lang('ACTIVATION_DESC'),
                        'options' => [
                            [
                                'label' => Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', Text::_($globalUserActivation)),
                                'value' => 'use_global'
                            ],
                            [
                                'label' => Text::_('JNONE'),
                                'value' => '0'
                            ],
                            [
                                'label' => Text::_('COM_USERS_CONFIG_FIELD_USERACTIVATION_OPTION_SELFACTIVATION'),
                                'value' => '1'
                            ],
                            [
                                'label' => Text::_('COM_USERS_CONFIG_FIELD_USERACTIVATION_OPTION_ADMINACTIVATION'),
                                'value' => '2'
                            ],
                        ]
                    ]
                ]
            ]
        ];
	}

    /**
     * Method to create a Joomla user account
     *
     * @param array $temp  The user data
     * 
	 * @return  mixed  The user id on success, false on failure.
     */
    private function register($temp)
    {
        Factory::getLanguage()->load('com_users');

        $data = [
            'name'   	=> $temp['name'],
            'username'	=> $temp['username'],
            'password'	=> $temp['password'],
            'password2'	=> $temp['password'],
            'email'		=> JStringPunycode::emailToPunycode($temp['email']),
            'groups'	=> $temp['groups']
        ];

        // Get Users component configuration options
		$params = ComponentHelper::getParams('com_users');

        $useractivation = (int) ($temp['activation'] == 'use_global' ? $params->get('useractivation') : $temp['activation']);
		$sendpassword = $params->get('sendpassword', 1);

		// Check if the user needs to activate their account.
		if ($useractivation == 1 || $useractivation == 2)
		{
			$data['activation'] = ApplicationHelper::getHash(UserHelper::genRandomPassword());
			$data['block'] = 1;
		}
        
        // Load the users plugin group.
        $user = new User;

        // Bind the data.
        if (!$user->bind($data))
        {
            $this->setError($user->getError());
            return false;
        }
        
        // Load the users plugin group.
		PluginHelper::importPlugin('user');

        if (!$user->save())
        {
            $this->setError(Text::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $user->getError()));
            return false;
        }

        // Send confirmation emails
		$app = Factory::getApplication();
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

        // Compile the notification mail values.
        $data = $user->getProperties();
        $data['fromname'] = $app->get('fromname');
        $data['mailfrom'] = $app->get('mailfrom');
        $data['sitename'] = $app->get('sitename');
        $data['siteurl'] = Uri::root();

        // Handle account activation/confirmation emails.
        if ($useractivation == 2)
        {
            // Set the link to confirm the user email.
            $linkMode = $app->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;

            $data['activate'] = Route::link(
                'site',
                'index.php?option=com_users&task=registration.activate&token=' . $data['activation'],
                false,
                $linkMode,
                true
            );

            $mailtemplate = 'com_users.registration.user.admin_activation';
        }
        elseif ($useractivation == 1)
        {
            // Set the link to activate the user account.
            $linkMode = $app->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;

            $data['activate'] = Route::link(
                'site',
                'index.php?option=com_users&task=registration.activate&token=' . $data['activation'],
                false,
                $linkMode,
                true
            );

            $mailtemplate = 'com_users.registration.user.self_activation';
        }
        else
        {
            $mailtemplate = 'com_users.registration.user.registration_mail';
        }

        if ($sendpassword)
        {
            $mailtemplate .= '_w_pw';
        }

        // Try to send the registration email.
        try
        {
            if (defined('nrJ4'))
            {
                $mailer = new MailTemplate($mailtemplate, $app->getLanguage()->getTag());
                $mailer->addTemplateData($data);
                $mailer->addRecipient($data['email']);
                $mailer->send();
            } else
            {
                $this->Joomla3SendRegistrationEmails($data, $useractivation, $sendpassword);
            }
        }
        catch (\Exception $exception)
        {
            $this->setError(Text::_($exception->getMessage()));
            return false;
        }
        
        return $user;
    }

    private function Joomla3SendRegistrationEmails($data, $useractivation, $sendpassword)
    {
        switch ($useractivation)
        {
            case 1:
                $emailSubject = Text::sprintf(
                    'COM_USERS_EMAIL_ACCOUNT_DETAILS',
                    $data['name'],
                    $data['sitename']
                );
    
                if ($sendpassword)
                {
                    $emailBody = Text::sprintf(
                        'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
                        $data['name'],
                        $data['sitename'],
                        $data['activate'],
                        $data['siteurl'],
                        $data['username'],
                        $data['password_clear']
                    );
                }
                else
                {
                    $emailBody = Text::sprintf(
                        'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY_NOPW',
                        $data['name'],
                        $data['sitename'],
                        $data['activate'],
                        $data['siteurl'],
                        $data['username']
                    );
                }
                break;

            case 2:
                $emailSubject = Text::sprintf(
                    'COM_USERS_EMAIL_ACCOUNT_DETAILS',
                    $data['name'],
                    $data['sitename']
                );
    
                if ($sendpassword)
                {
                    $emailBody = Text::sprintf(
                        'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY',
                        $data['name'],
                        $data['sitename'],
                        $data['activate'],
                        $data['siteurl'],
                        $data['username'],
                        $data['password_clear']
                    );
                }
                else
                {
                    $emailBody = Text::sprintf(
                        'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY_NOPW',
                        $data['name'],
                        $data['sitename'],
                        $data['activate'],
                        $data['siteurl'],
                        $data['username']
                    );
                }
                break;
               
            default:
                $emailSubject = Text::sprintf(
                    'COM_USERS_EMAIL_ACCOUNT_DETAILS',
                    $data['name'],
                    $data['sitename']
                );

                if ($sendpassword)
                {
                    $emailBody = Text::sprintf(
                        'COM_USERS_EMAIL_REGISTERED_BODY',
                        $data['name'],
                        $data['sitename'],
                        $data['siteurl'],
                        $data['username'],
                        $data['password_clear']
                    );
                }
                else
                {
                    $emailBody = Text::sprintf(
                        'COM_USERS_EMAIL_REGISTERED_BODY_NOPW',
                        $data['name'],
                        $data['sitename'],
                        $data['siteurl']
                    );
                }
        }

        // Send the registration email.
		Factory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);
    }

    /**
     * API endpoint that returns a list of all user groups of the site
     *
     * @return array
     */
    private function getGroups()
    {
        $groups = \Joomla\CMS\Helper\UserGroupsHelper::getInstance()->getAll();
        $grps = [];

        foreach ($groups as $group)
        {
            $grps[] = [
                'value' => $group->id,
                'label' => str_repeat('- ', $group->level) . $group->title
            ];
        }

        return $grps;
    }
}