/* jce - 2.9.55 | 2023-11-28 | https://www.joomlacontenteditor.net | Copyright (C) 2006 - 2023 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
!function() {
    var DOM = tinymce.DOM, Event = tinymce.dom.Event;
    tinymce.extend, tinymce.html.SaxParser, tinymce.html.Schema;
    tinymce.create("tinymce.plugins.SourcePlugin", {
        init: function(ed, url) {
            var self = this;
            function isEditorActive() {
                return 0 == DOM.hasClass(ed.getElement(), "wf-no-editor");
            }
            (self.editor = ed).plugins.fullscreen && (ed.onFullScreen.add(function(ed, state) {
                var element = ed.getElement(), element = DOM.getPrev(element, ".wf-editor-header"), iframe = DOM.get(ed.id + "_editor_source_iframe");
                state ? (ed.settings.container_height || (ed.settings.container_height = iframe.clientHeight), 
                state = DOM.getViewPort(), DOM.setStyle(iframe, "height", state.h - element.offsetHeight - 42), 
                DOM.setStyle(iframe, "max-width", "100%")) : (DOM.setStyle(iframe, "height", ed.settings.container_height - 42), 
                DOM.setStyle(iframe, "max-width", ed.settings.container_width || "100%")), 
                iframe && (state = iframe.contentWindow.SourceEditor, element = iframe.clientWidth, 
                ed = iframe.clientHeight, state.resize(element, ed));
            }), ed.onFullScreenResize.add(function(ed, vp) {
                var element = ed.getElement(), element = DOM.getPrev(element, ".wf-editor-header"), ed = DOM.get(ed.id + "_editor_source_iframe");
                DOM.setStyle(ed, "height", vp.h - element.offsetHeight - 42);
            })), ed.onSetContent.add(function(ed, o) {
                self.setContent(ed.getContent(), !0);
            }), ed.onInit.add(function(ed) {
                0 != isEditorActive() && "wf-editor-source" === (ed.settings.active_tab || "") && (DOM.hide(ed.getContainer()), 
                DOM.hide(ed.getElement()), window.setTimeout(function() {
                    self.toggle();
                }, 10));
            }), this.ControlManager = new tinymce.ControlManager(ed);
        },
        getSourceEditor: function() {
            var ed = this.editor, ed = DOM.get(ed.id + "_editor_source_iframe");
            return ed && ed.contentWindow.SourceEditor || null;
        },
        setContent: function(v) {
            var editor = this.getSourceEditor();
            return !!editor && editor.setContent(v);
        },
        insertContent: function(v) {
            var editor = this.getSourceEditor();
            return !!editor && editor.insertContent(v);
        },
        getContent: function() {
            var ed = this.editor, editor = this.getSourceEditor();
            return editor && !DOM.isHidden(ed.id + "_editor_source") ? editor.getContent() : null;
        },
        hide: function() {
            var ed = this.editor, editor = this.getSourceEditor();
            editor && editor.setActive(!1), DOM.hide(ed.id + "_editor_source");
        },
        getActionState: function(key, value) {
            if (!1 !== this.editor.settings.use_state_cookies) {
                key = tinymce.util.Storage.get("wf_source_" + key);
                if (tinymce.is(key) && null !== key) return parseInt(key, 10);
            }
            return value;
        },
        setActionState: function(key, value) {
            !1 !== this.editor.settings.use_state_cookies && tinymce.util.Storage.set("wf_source_" + key, value ? 1 : 0);
        },
        save: function(content, debounced) {
            var ed = this.editor, el = ed.getElement(), content = {
                content: content = tinymce.is(content) ? content : this.getContent(),
                no_events: !0,
                format: "raw"
            };
            return !1 !== ed.settings.source_validate_content && ed.onWfEditorSave.dispatch(ed, content), 
            /TEXTAREA|INPUT/i.test(el.nodeName) ? el.value = content.content : el.innerHTML = content.content, 
            debounced && ed.onWfEditorChange.dispatch(ed, content), content.content;
        },
        getActiveLine: function() {
            var ed = this.editor, blocks = [], line = 0, node = (tinymce.each(ed.schema.getBlockElements(), function(value, name) {
                if (/\W/.test(name)) return !0;
                blocks.push(name.toLowerCase());
            }), ed.selection.getNode()), nodes = ed.getBody().querySelectorAll(blocks.join(","));
            if (node) {
                1 === node.nodeType && "bookmark" !== node.getAttribute("data-mce-type") || (node = node.parentNode);
                for (var i = 0, len = nodes.length; i < len; i++) if (nodes[i] === node) {
                    line = i;
                    break;
                }
            }
            return line;
        },
        createToolbar: function(container) {
            var cm, replaceBox, fullscreen_btn, self = this, ed = this.editor, container = DOM.add(container, "div", {
                class: "mceToolbar mceToolbarSource"
            }), toolbarRow = self.ControlManager.createToolbar("source_toolbar", {
                name: ed.getLang("advanced.toolbar"),
                tab_focus_toolbar: ed.getParam("theme_advanced_tab_focus_toolbar"),
                class: "mceFlex mceFlexAuto"
            }), toolbarActions = self.ControlManager.createToolbar("source_toolbar_actions", {
                class: "mceSourceActions"
            }), format_btn = (ed.plugins.fullscreen && ((fullscreen_btn = self.ControlManager.createButton("source_fullscreen", {
                title: ed.getLang("source.fullscreen", "Fullscreen"),
                onclick: function() {
                    var state = !fullscreen_btn.isActive();
                    return fullscreen_btn.setActive(state), ed.execCommand("mceFullScreen");
                }
            })).setActive(ed.fullscreen_enabled), toolbarActions.add(fullscreen_btn)), 
            tinymce.each([ "undo", "redo" ], function(name) {
                var btn = self.ControlManager.createButton("source_" + name, {
                    title: ed.getLang("advanced." + name + "_desc", name),
                    onclick: function() {
                        cm = cm || self.getSourceEditor(), "undo" == name && self.ControlManager.get("source_redo").setDisabled(!1);
                        var state = cm.execCommand(name);
                        btn.setDisabled(!state);
                    }
                });
                btn.onPostRender.add(function(ctrl, el) {
                    ctrl.setDisabled(!0);
                }), toolbarActions.add(btn);
            }), tinymce.each([ "highlight", "linenumbers", "wrap" ], function(name) {
                var btn = self.ControlManager.createButton("source_" + name, {
                    title: ed.getLang("source." + name, name),
                    onclick: function() {
                        var state = !btn.isActive();
                        return btn.setActive(state), self.setActionState(name, state), 
                        (cm = cm || self.getSourceEditor()).execCommand(name, state);
                    }
                });
                btn.onPostRender.add(function() {
                    var state = self.getActionState(name, ed.getParam("source_" + name, !0));
                    btn.setActive(!!state);
                }), toolbarActions.add(btn);
            }), self.ControlManager.createButton("source_format", {
                title: ed.getLang("source.format", "Format"),
                onclick: function() {
                    return (cm = cm || self.getSourceEditor()).execCommand("format");
                }
            })), toolbarSearch = (toolbarActions.add(format_btn), toolbarRow.add(toolbarActions), 
            self.ControlManager.createToolbar("source_toolbar_search", {
                class: "mceSourceSearch"
            })), searchBox = self.ControlManager.createTextBox("source_search_value", {
                title: ed.getLang("source.search", "Search"),
                attributes: {
                    placeholder: ed.getLang("source.search_value", "Search")
                }
            }), regexBtnState = (searchBox.onChange.add(function() {
                if ("" === searchBox.value()) return (cm = cm || self.getSourceEditor()).execCommand("clearSearch");
            }), searchBox.onPostRender.add(function(e, elm) {
                Event.add(elm, "keydown", function(e) {
                    if (13 === e.keyCode) {
                        e.preventDefault();
                        e = searchBox.value();
                        if ("" === e) return !1;
                        cm = cm || self.getSourceEditor();
                        var regex = regexBtn.isActive();
                        return cm.execCommand("search", e, !0, !!regex);
                    }
                });
            }), toolbarSearch.add(searchBox), tinymce.each({
                previous: "search_prev",
                next: "search"
            }, function(label, name) {
                label = self.ControlManager.createButton("source_search_" + name, {
                    title: ed.getLang("source." + label, name),
                    onclick: function(e) {
                        cm = cm || self.getSourceEditor();
                        var value = searchBox.value(), regex = regexBtn.isActive();
                        return cm.execCommand("search", value, "previous" === name, !!regex);
                    }
                });
                toolbarSearch.add(label);
            }), (replaceBox = self.ControlManager.createTextBox("source_replace_value", {
                title: ed.getLang("source.replace", "Replace"),
                attributes: {
                    placeholder: ed.getLang("source.replace_value", "Replace")
                }
            })).onPostRender.add(function(e, elm) {
                Event.add(elm, "keydown", function(e) {
                    if (13 === e.keyCode) {
                        e.preventDefault();
                        e = searchBox.value();
                        if ("" === e) return !1;
                        cm = cm || self.getSourceEditor();
                        var replace = replaceBox.value(), regex = regexBtn.isActive();
                        return cm.execCommand("replace", e, replace, !1, !!regex);
                    }
                });
            }), toolbarSearch.add(replaceBox), tinymce.each([ "replace", "replace_all" ], function(name) {
                var btn = self.ControlManager.createButton("source_" + name, {
                    title: ed.getLang("source." + name, name),
                    onclick: function() {
                        cm = cm || self.getSourceEditor();
                        var value = searchBox.value(), replace = replaceBox.value(), regex = regexBtn.isActive();
                        return cm.execCommand("replace", value, replace, "replace_all" === name, !!regex);
                    }
                });
                toolbarSearch.add(btn);
            }), !1), regexBtn = self.ControlManager.createButton("source_search_regex", {
                title: ed.getLang("source.search_regex", "Regular Expression"),
                onclick: function() {
                    regexBtnState = !regexBtnState, regexBtn.setActive(regexBtnState);
                }
            });
            toolbarSearch.add(regexBtn), toolbarRow.add(toolbarSearch), toolbarRow.renderTo(container), 
            self.ControlManager.onPostRender.dispatch();
        },
        toggle: function() {
            var ed = this.editor, self = this, s = ed.settings, element = ed.getElement(), container = element.parentNode, header = DOM.getPrev(element, ".wf-editor-header"), div = DOM.get(ed.id + "_editor_source"), iframe = DOM.get(ed.id + "_editor_source_iframe"), statusbar = DOM.get(ed.id + "_editor_source_resize"), ifrHeight = parseInt(DOM.get(ed.id + "_ifr").style.height, 10) || s.height, o = tinymce.util.Storage.getHash("TinyMCE_" + ed.id + "_size");
            o && o.height && (ifrHeight = o.height);
            var content = (tinymce.is(element.value) ? element.value : element.innerHTML).replace(/<br data-mce-bogus="1"([^>]+)>/gi, ""), selection = "", line = this.getActiveLine();
            if (ed.selection.isCollapsed() || (o = ed.selection.getNode()) !== ed.getBody() && (selection = o.outerHTML), 
            div) {
                DOM.show(div);
                var editor = iframe.contentWindow.SourceEditor, element = iframe.clientWidth, o = iframe.clientHeight;
                editor.resize(element, o), editor.setContent(content, !0), selection && editor.setSelection(line, selection), 
                editor.setCursor(line), editor.setActive(!0), DOM.removeClass(container, "mce-loading");
            } else {
                var key, div = DOM.add(container, "div", {
                    role: "textbox",
                    id: ed.id + "_editor_source",
                    class: "wf-editor-source"
                }), element = s.skin_class || "defaultSkin", query = (DOM.addClass(div, element), 
                ed.getParam("site_url") + "index.php?option=com_jce"), args = {
                    task: "plugin.display",
                    plugin: "source"
                };
                for (key in args) query += "&" + key + "=" + encodeURIComponent(args[key]);
                var iframe = DOM.create("iframe", {
                    frameborder: 0,
                    scrolling: "no",
                    id: ed.id + "_editor_source_iframe",
                    src: query + "&" + ed.settings.query
                }), o = (Event.add(iframe, "load", function() {
                    var editor = iframe.contentWindow.SourceEditor, w = iframe.clientWidth, h = iframe.clientHeight, options = {
                        theme: ed.getParam("source_theme", "codemirror"),
                        format: ed.getParam("source_format", !0),
                        tag_closing: ed.getParam("source_tag_closing", !0),
                        selection_match: ed.getParam("source_selection_match", !0),
                        font_size: ed.getParam("source_font_size", ""),
                        fullscreen: DOM.hasClass(container, "mce-fullscreen"),
                        load: function() {
                            DOM.removeClass(container, "mce-loading"), editor.resize(w, h), 
                            selection && editor.setSelection(line, selection), editor.setCursor(line);
                        },
                        change: function() {
                            self.ControlManager.get("source_undo").setDisabled(!1);
                        },
                        format_options: ed.getParam("source_format_options", {}),
                        editor: ed
                    };
                    tinymce.each([ "wrap", "linenumbers", "highlight" ], function(key) {
                        options[key] = self.getActionState(key, ed.getParam("source_" + key, !0));
                    }), editor.init(options, content);
                }), this.createToolbar(div), DOM.add(div, "div", {
                    class: "mceIframeContainer"
                })), resize = (DOM.add(o, iframe), statusbar = DOM.add(div, "div", {
                    id: ed.id + "_editor_source_statusbar",
                    class: "mceStatusbar mceLast"
                }, '<div class="mcePathRow"></div><div tabindex="-1" class="mceResize" id="' + ed.id + '_editor_source_resize"><span class="mceIcon mce_resize"></span></div>'), 
                DOM.get(ed.id + "_editor_source_resize"));
                Event.add(resize, "click", function(e) {
                    e.preventDefault();
                }), Event.add(resize, "mousedown", function(e) {
                    var mm1, mm2, mu1, mu2, sx, sy, sw, sh, w, h, ifrDoc = iframe.contentWindow.document, editor = iframe.contentWindow.SourceEditor;
                    function resizeTo(w, h) {
                        w = Math.max(w, 300), h = Math.max(h, 200), iframe.style.maxWidth = w + "px", 
                        iframe.style.height = h + "px", container.style.maxWidth = w + "px", 
                        editor.resize(w, h), ed.settings.container_width = w, ed.settings.container_height = h + statusbar.offsetHeight, 
                        h -= ed.settings.interface_height || 0, ed.theme.resizeTo(w, h);
                    }
                    function resizeOnMove(e) {
                        e.preventDefault(), w = sw + (e.screenX - sx), h = sh + (e.screenY - sy), 
                        resizeTo(w, h), DOM.addClass(resize, "wf-editor-source-resizing");
                    }
                    function endResize(e) {
                        e.preventDefault(), Event.remove(DOM.doc, "mousemove", mm1), 
                        Event.remove(DOM.doc, "mouseup", mu1), Event.remove(ifrDoc, "mousemove", mm2), 
                        Event.remove(ifrDoc, "mouseup", mu2), w = sw + (e.screenX - sx), 
                        h = sh + (e.screenY - sy), resizeTo(w, h), DOM.removeClass(resize, "wf-editor-source-resizing");
                    }
                    if (e.preventDefault(), DOM.hasClass(resize, "wf-editor-source-resizing")) return endResize(e), 
                    !1;
                    sx = e.screenX, sy = e.screenY, sw = w = container.offsetWidth, 
                    sh = h = iframe.clientHeight, mm1 = Event.add(DOM.doc, "mousemove", resizeOnMove), 
                    mu1 = Event.add(DOM.doc, "mouseup", endResize), mm2 = Event.add(ifrDoc, "mousemove", resizeOnMove), 
                    mu2 = Event.add(ifrDoc, "mouseup", endResize);
                });
            }
            s = ed.settings.container_height || sessionStorage.getItem("wf-editor-container-height") || ifrHeight + statusbar.offsetHeight, 
            element = ed.settings.container_width || sessionStorage.getItem("wf-editor-container-width");
            DOM.hasClass(container, "mce-fullscreen") && (s = DOM.getViewPort().h - header.offsetHeight), 
            DOM.setStyle(iframe, "height", s - statusbar.offsetHeight), (editor = iframe.contentWindow.SourceEditor) && editor.resize(element, s - statusbar.offsetHeight);
        },
        getCursorPos: function() {
            var iframe = DOM.get(this.editor.id + "_editor_source_iframe");
            if (iframe) {
                iframe = iframe.contentWindow.SourceEditor;
                if (iframe) return iframe.getCursorPos();
            }
            return 0;
        }
    }), tinymce.PluginManager.add("source", tinymce.plugins.SourcePlugin);
}();