/* jce - 2.9.55 | 2023-11-28 | https://www.joomlacontenteditor.net | Copyright (C) 2006 - 2023 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
!function() {
    var each = tinymce.each;
    tinymce.create("tinymce.plugins.FontSelectPlugin", {
        fonts: "Andale Mono=andale mono,times;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Symbol=symbol;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Trebuchet MS=trebuchet ms,geneva;Verdana=verdana,geneva;Webdings=webdings;Wingdings=wingdings,zapf dingbats",
        init: function(ed, url) {
            (this.editor = ed).onNodeChange.add(function(ed, cm, n, collapsed, o) {
                var fv, c = cm.get("fontselect");
                c && n && each(o.parents, function(n) {
                    if (n.style && (fv = (fv = n.style.fontFamily || ed.dom.getStyle(n, "fontFamily")).replace(/[\"\']+/g, "").replace(/^([^,]+).*/, "$1").toLowerCase(), 
                    c.select(function(v) {
                        return v.replace(/^([^,]+).*/, "$1").toLowerCase() === fv;
                    }), fv)) return !1;
                });
            });
        },
        createControl: function(n, cf) {
            if ("fontselect" === n) return this._createFontSelect();
        },
        _createFontSelect: function() {
            var ed = this.editor, ctrl = ed.controlManager.createListBox("fontselect", {
                title: "advanced.fontdefault",
                max_height: 384,
                onselect: function(v) {
                    var cur = ctrl.items[ctrl.selectedIndex];
                    !v && cur ? ed.execCommand("FontName", !1, cur.value) : (ed.execCommand("FontName", !1, v), 
                    ctrl.select(function(sv) {
                        return v == sv;
                    }), cur && cur.value == v && ctrl.select(null));
                }
            });
            return each(ed.getParam("fontselect_fonts", this.fonts, "hash"), function(v, k) {
                /\d/.test(v) && (v = "'" + v + "'"), ctrl.add(ed.translate(k), v, {
                    style: -1 == v.indexOf("dings") ? "font-family:" + v : ""
                });
            }), ctrl;
        }
    }), tinymce.PluginManager.add("fontselect", tinymce.plugins.FontSelectPlugin);
}();