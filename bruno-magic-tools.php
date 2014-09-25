<?php
/*
Plugin Name: Bruno Magic Tools
Plugin URI: http://www.brunoxu.com/bruno-magic-tools.html
Description: Add addtional functions to advance current theme, avoid loss during theme's upgrade.
Author: Bruno Xu
Author URI: http://www.brunoxu.com/
Version: 1.0
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

define('BRUNO_MAGIC_TOOLS_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('BRUNO_MAGIC_TOOLS_PLUGIN_DIR', plugin_dir_path( __FILE__ ));

/* for reference

add_filter('excerpt_length', 'bruno_magic_tools_excerpt_length', 999);
function bruno_magic_tools_excerpt_length($number) {// default: 55
	return 180;
}

add_filter('excerpt_more', 'bruno_magic_tools_excerpt_more', 999);
function bruno_magic_tools_excerpt_more($more_string) {// default: ' ' . '[&hellip;]'
	return ' ' . '[&hellip;]';
}

add_filter('wp_trim_words', 'bruno_magic_tools_trim_words', 9 ,4);
function bruno_magic_tools_trim_words($text, $num_words, $more, $original_text) {
	$text_bak = $text;
	$more_removed = FALSE;
	if ($more && stripos($text, $more)!==FALSE && substr($text, strlen($more)*-1)==$more) {
		$more_removed = TRUE;
		$text = substr($text, 0, strlen($text)-strlen($more));
	}
	if (empty($more)) {
		$more = '...';
	}
	if (function_exists('mb_strimwidth')) {
		return mb_strimwidth($text, 0, $num_words, $more, 'utf8');
	}
	return $text_bak;
}

add_filter('wp_trim_excerpt', 'bruno_magic_tools_trim_excerpt', 9 ,2);
function bruno_magic_tools_trim_excerpt($text, $raw_excerpt) {
	if (has_excerpt() && $raw_excerpt) {
		$text = wp_strip_all_tags( $raw_excerpt );
	}
	return $text;
}



add_filter('the_content_more_link', 'bruno_magic_tools_content_more');
// default: ' <a href="' . get_permalink() . "#more-{$post->ID}\" class=\"more-link\">$more_link_text</a>"
// default: $more_link_text
function bruno_magic_tools_content_more($more_link_element, $more_link_text) {
	//return str_ireplace($more_link_text, '查看更多', $more_link_element);
	return '';
}

没有get_the_content filter

the_content filter

*/

/** travelify: 为了列表页post文字数量正常 */
add_filter('excerpt_length', 'bruno_magic_tools_excerpt_length',999);
function bruno_magic_tools_excerpt_length($number) {// default: 55
	return 300;
}
/******************/
add_filter('wp_trim_words', 'bruno_magic_tools_trim_words', 9 ,4);
function bruno_magic_tools_trim_words($text, $num_words, $more, $original_text) {
	$text_bak = $text;
	$more_removed = FALSE;
	if ($more && stripos($text, $more)!==FALSE && substr($text, strlen($more)*-1)==$more) {
		$more_removed = TRUE;
		$text = substr($text, 0, strlen($text)-strlen($more));
	}
	if (function_exists('mb_strimwidth')) {
		if (function_exists('mb_strlen') && abs(strlen($text)-mb_strlen($text, 'utf8'))<10) {
			return $text_bak;
		}
		$text = mb_strimwidth($text, 0, $num_words, $more, 'utf8');
		if (function_exists('mb_strlen') && mb_strlen($text, 'utf8')<=$num_words) {
			$text .= ($more_removed?$more:'');
		}
		return $text;
	}
	return $text_bak;
}
/** travelify: 为了列表页post文字数量正常 END */


/** travelify: related posts */
add_action('wp_head', 'bruno_magic_tools_related_posts_css');
function bruno_magic_tools_related_posts_css() {
?>
<style>
ul.related_posts{
list-style: none !important;
margin: 0 !important;
}
ul.related_posts li{
float: left;
width: 24%;
margin-right: 1%;
margin-bottom: 1%;
}
ul.related_posts li span{
display: block;
height: 50px;
overflow: hidden;
}
ul.related_posts li img{width:100%}
</style>
<?php
}
//add_action('travelify_after_loop_content', 'bruno_magic_tools_related_posts', 11);
add_action('travelify_loop_content', 'bruno_magic_tools_related_posts', 11);
function bruno_magic_tools_related_posts() {
	if (!is_single()) return;
	include_once 'wpjam.php';
	include_once 'wpjam-posts.php';
	wpjam_related_posts(8, array('class'=>'related_posts clearfix'));
}
/** travelify: related posts END */


/** travelify: sidebar_follow_func */
add_action('wp_footer', 'bruno_magic_tools_sidebar_follow');
function bruno_magic_tools_sidebar_follow() {
?>
<script>
var init_width = 0;
var switch_height = 0;
var sidebar_fixed = false;
var sidebar_fixed_top = 50;
jQuery(document).ready(function($) {
	_sidebar_fix = $('#recent-posts-2');
	//_sidebar_prev = $('#text-7');
	_sidebar_prev = $('#secondary .widget:not(#recent-posts-2):last');
	if (_sidebar_fix.length <= 0) {
		return;
	}
	if (_sidebar_prev.length <= 0) {
		return;
	}
	if ($(document).width() <= 768) {
		return;
	}
	window.sidebar_follow_func = function() {
		if (switch_height <= 0) {
			switch_height = _sidebar_prev.offset().top+_sidebar_prev.height();
		}
		if ( switch_height < $(document).scrollTop() ) {
			if (!sidebar_fixed) {
				if (init_width <= 0) {
					init_width = _sidebar_fix.width();
				}
				_sidebar_fix.css('width',init_width)
					.css('position','fixed')
					.css('top',sidebar_fixed_top);
				sidebar_fixed = true;
			}
		} else {
			if (sidebar_fixed) {
				_sidebar_fix.css('width','')
					.css('position','')
					.css('top','');
				sidebar_fixed = false;
			}
		}
	}
	var tout_handler;
	$(window).scroll(function(){clearTimeout(tout_handler);tout_handler=setTimeout(sidebar_follow_func,100);});
	$(window).resize(function(){clearTimeout(tout_handler);tout_handler=setTimeout(sidebar_follow_func,100);});
});
</script>
<?php
}
/** travelify: sidebar_follow_func END */


/** all: 文章标题添加new图标 */if (false) {
if (!is_admin()) {
	if (!function_exists('gmstrtotime')) {
		/*function gmstrtotime($s) {// http://www.kuqin.com/php5_doc/function.strtotime.html
			$t = strtotime($s);
			$zone = intval(date("O"))/100;
			$t += $zone*60*60;
			return $t;
		}*/
		function gmstrtotime($s) {// http://php.net/manual/zh/function.strtotime.php
			return(strtotime($s . " UTC"));
		}
	}
	add_filter('the_title', 'bruno_magic_tools_add_new_to_title', 10, 2);
	function bruno_magic_tools_add_new_to_title($title, $id) {
		if (!is_home() && !is_category() && !is_archive() && !is_search() && !is_tag()) return $title;
		if (!in_the_loop()) return $title;

		//global $post; // not work properly in 'Twenty Thirteen' theme, main nav affected too.
		$post = get_post($id);

		if ($post->post_type != 'post') {
			return $title;
		}

		$days = 3;

		//$new = '<em>New</em>';
		$new = '<img src="http://img.lanrentuku.com/img/allimg/1206/5-120601152045.png" />';

		$now_timestamp = time();
		$post_timestamp = gmstrtotime($post->post_date_gmt);
		if ( $post_timestamp<=$now_timestamp && ($now_timestamp-$post_timestamp)<(86400*$days) ) {
			return $title.$new;
		}
		return $title;
	}
}
}/** all: 文章标题添加new图标 END */


/** all: debug in out buffering */
function ob_debug($var, $die=TRUE) {
	$handle = fopen(plugin_dir_path(__FILE__).date('Y-m-d').'.log', 'a+');
	fwrite($handle, "\n\n\n--------------------\n");
	fwrite($handle, json_encode($var));
	fclose($handle);
	if ($die) die();
}
/*
// http://php.net/manual/en/function.ob-start.php    When a fatal error is thrown, PHP will output the current buffer of Output-Control without postprocessing before printing the error message. If you are working with several output control levels, this might not result in the desired behavior.
ob_start("ob_error_handler");
function ob_error_handler($str) {
    $error = error_get_last();
    if ($error && $error["type"] == E_USER_ERROR || $error["type"] == E_ERROR) {
        return ini_get("error_prepend_string").
          "\nFatal error: $error[message] in $error[file] on line $error[line]\n".
          ini_get("error_append_string");
    }
    return $str;
}*/
/** all: debug in out buffering END */


/** all: set baidu share datas:bdText,bdDesc,bdUrl,bdPic */
$bd_share_js = '
<div class="bdsharebuttonbox">
<a href="#" class="bds_qzone" data-cmd="qzone" title="分享到QQ空间"></a>
<a href="#" class="bds_tsina" data-cmd="tsina" title="分享到新浪微博"></a>
<a href="#" class="bds_tqq" data-cmd="tqq" title="分享到腾讯微博"></a>
<a href="#" class="bds_renren" data-cmd="renren" title="分享到人人网"></a>
<a href="#" class="bds_weixin" data-cmd="weixin" title="分享到微信"></a>
<a href="#" class="bds_bdhome" data-cmd="bdhome" title="分享到百度新首页"></a>
<a href="#" class="bds_douban" data-cmd="douban" title="分享到豆瓣网"></a>
<a href="#" class="bds_youdao" data-cmd="youdao" title="分享到有道云笔记"></a>
<a href="#" class="bds_sqq" data-cmd="sqq" title="分享到QQ好友"></a>
<a href="#" class="bds_t163" data-cmd="t163" title="分享到网易微博">
<a href="#" class="bds_more" data-cmd="more"></a>
</div><script>window._bd_share_config={"common":{"bdSnsKey":{},"bdText":"","bdMini":"2","bdMiniList":false,"bdPic":"","bdStyle":"0","bdSize":"32"},"share":{}};with(document)0[(getElementsByTagName("head")[0]||body).appendChild(createElement("script")).src="http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion="+~(-new Date()/36e5)];</script>
';
add_filter('the_content', 'bruno_magic_tools_baidu_share_datas');
function bruno_magic_tools_baidu_share_datas($content) {
	global $bdText,$bdDesc,$bdUrl,$bdPic;
	$bdText='';
	$bdDesc='';
	$bdUrl='';
	$bdPic='';

	if (is_single() || is_page()) {
		global $post;

		//$bdText = mb_strimwidth($post->post_title, 0, 52, '...', 'utf8');
		//$bdText = mb_substr($post->post_title, 0, 52, 'utf8');
		$bdText = mb_strimwidth($post->post_title, 0, 80, '...', 'utf8');

		if (has_excerpt($post)) {
			$cont = strip_tags($post->post_excerpt);
		} else {
			$cont = strip_tags(strip_shortcodes($post->post_content));
		}
		$cont = trim($cont);
		//$bdDesc = mb_strimwidth($cont, 0, 100, '...', 'utf8');//前面做了Save Yupoo Imgs To Local插件，可以保存又拍图片到本地，页面显示的时候替换成本地地址，文章...
		//$bdDesc = mb_substr($cont, 0, 100, 'utf8');//前面做了Save Yupoo Imgs To Local插件，可以保存又拍图片到本地，页面显示的时候替换成本地地址，文章的实际内容没变，还是引用的yupoo图片，换种说法是数据库中的数据没变，如果需要
		//$bdDesc = mb_substr($cont, 0, 120, 'utf8').'...';
		$bdDesc = mb_strimwidth($cont, 0, 200, '...', 'utf8');

		$bdUrl = get_permalink($post);

		$imgs = array();
		preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', $post->post_content, $matches);
		if (has_post_thumbnail($post->ID)) {
			$imgs[] = wp_get_attachment_url(get_post_meta($post->ID,'_thumbnail_id',true));
		} elseif ($matches) {
			foreach ($matches[1] as $src) {
				$imgs[] = $src;
				if (count($imgs) >= 1) {
					break;
				}
			}
		}
		if ($imgs) {
			$bdPic = implode('|', $imgs);
		}

		global $bd_share_js;
		return $content.$bd_share_js;
	}
	return $content;
}
add_action('wp_footer', 'bruno_magic_tools_baidu_share_datas_output');
function bruno_magic_tools_baidu_share_datas_output() {
	global $bdText,$bdDesc,$bdUrl,$bdPic;
	if (empty($bdText) || empty($bdDesc)) return;
	echo '
<script type="text/javascript">
if (window._bd_share_config) {
	window._bd_share_config["common"]["bdText"] = '.json_encode($bdText).';
	window._bd_share_config["common"]["bdDesc"] = '.json_encode($bdDesc).';
	window._bd_share_config["common"]["bdUrl"] = '.json_encode($bdUrl).';
	window._bd_share_config["common"]["bdPic"] = '.json_encode($bdPic).';
}
</script>
';
}
/** all: set baidu share datas:bdText,bdDesc,bdUrl,bdPic END */


/** all: fix images' height as width been set as 100%, for lazy load displaying */
add_action('wp_footer','bruno_magic_tools_fix_images_height',0);
function bruno_magic_tools_fix_images_height() {
?>
<script>
jQuery(function($) {
	window.calc_image_height = function(_img) {
		var width = _img.attr('width');
		var height = _img.attr('height');
		if ( !(width && height) ) return;
		var now_width = _img.width();
		var now_height = parseInt(height * (now_width/width));
		_img.css('height', now_height);
	}
	window.fix_images_height = function() {
		$('.home #primary #content .post .post-featured-image>a>img,.single #primary #content .entry-content img.size-full,.single #primary #content .entry-content img.size-large').each(function() {
			window.calc_image_height($(this));
		});
	}
	window.fix_images_height();
	$(window).resize(window.fix_images_height);
});
</script>
<?php
}
/** all: fix images' height as width been set as 100%, for lazy load displaying END */


/** Remove Google Fonts References(plugin): remove_google_fonts_priority filter */if (false) {
add_filter('remove_google_fonts_priority', 'bruno_magic_tools_rgfpriority');
function bruno_magic_tools_rgfpriority($priority) {
	//return 9 will make 'Remove Google Fonts References' running before 'Useso take over Google'
	return 9;
}
}/** Remove Google Fonts References(plugin): remove_google_fonts_priority filter END */


/** all: Prism syntax highlighter (wpjam version) */if (false) {
/*
Usage: <pre><code class="language-css">Your CSS Codes</code></pre> or <pre><code class="language-markup">Your HTML Codes</code></pre>
Support Types: language-markup, language-css, language-javascript, language-php
*/
add_filter('wp_footer', 'bruno_magic_tools_prism');
function bruno_magic_tools_prism() {
?>
<style>
code[class*="language-"],pre[class*="language-"]{color:black;text-shadow:0 1px white;font-family:Consolas,Monaco,'Andale Mono',monospace;direction:ltr;text-align:left;white-space:pre;word-spacing:normal;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-hyphens:none;-moz-hyphens:none;-ms-hyphens:none;hyphens:none}pre[class*="language-"]{overflow:auto}:not(pre)>code[class*="language-"],pre[class*="language-"]{background:#f5f2f0}:not(pre)>code[class*="language-"]{padding: .1em;border-radius: .3em}.token.comment,.token.prolog,.token.doctype,.token.cdata{color:slategray}.token.punctuation{color:#999}.namespace{opacity: .7}.token.property,.token.tag,.token.boolean,.token.number{color:#905}.token.selector,.token.attr-name,.token.string{color:#690}.token.operator,
.token.entity,
.token.url,
.language-css .token.string,
.style
.token.string{color:#a67f59}.token.atrule,.token.attr-value,.token.keyword{color:#07a}.token.regex,.token.important{color:#e90}.token.important{font-weight:bold}.token.entity{cursor:help}pre[data-line]{position:relative;padding:1em
0 1em 3em}.line-highlight{position:absolute;left:0;right:0;padding:inherit 0;margin-top:1em;background:hsla(24, 20%, 50%,.08);background:-moz-linear-gradient(left, hsla(24, 20%, 50%,.1) 70%, hsla(24, 20%, 50%,0));background:-webkit-linear-gradient(left, hsla(24, 20%, 50%,.1) 70%, hsla(24, 20%, 50%,0));background:-o-linear-gradient(left, hsla(24, 20%, 50%,.1) 70%, hsla(24, 20%, 50%,0));background:linear-gradient(left, hsla(24, 20%, 50%,.1) 70%,hsla(24,20%,50%,0));pointer-events:none;line-height:inherit;white-space:pre}.line-highlight:before,.line-highlight[data-end]:after{content:attr(data-start);position:absolute;top: .4em;left: .6em;min-width:1em;padding:0
.5em;background-color:hsla(24, 20%, 50%,.4);color:hsl(24, 20%, 95%);font:bold 65%/1.5 sans-serif;text-align:center;vertical-align: .3em;border-radius:999px;text-shadow:none;box-shadow:0 1px white}.line-highlight[data-end]:after{content:attr(data-end);top:auto;bottom: .4em}.token.tab:not(:empty):before,.token.cr:before,.token.lf:before{color:hsl(24,20%,85%)}.token.tab:not(:empty):before{content:'▸'}.token.cr:before{content:'␍'}.token.lf:before{content:'␊'}.token
a{color:inherit}.token.function,.token.constant{color:#07a}.token.variable{color:#e90}.token.deliminator{font-weight:bold}
</style>
<script>
(function() {
    var lang = /\blang(?:uage)?-(?!\*)(\w+)\b/i;
    var _ = self.Prism = {languages: {insertBefore: function(inside, before, insert, root) {
                root = root || _.languages;
                var grammar = root[inside];
                var ret = {};
                for (var token in grammar) {
                    if (grammar.hasOwnProperty(token)) {
                        if (token == before) {
                            for (var newToken in insert) {
                                if (insert.hasOwnProperty(newToken)) {
                                    ret[newToken] = insert[newToken];
                                }
                            }
                        }
                        ret[token] = grammar[token];
                    }
                }
                return root[inside] = ret;
            },DFS: function(o, callback) {
                for (var i in o) {
                    callback.call(o, i, o[i]);
                    if (Object.prototype.toString.call(o) === '[object Object]') {
                        _.languages.DFS(o[i], callback);
                    }
                }
            }},highlightAll: function(async, callback) {
            var elements = document.querySelectorAll('code[class*="language-"], [class*="language-"] code, code[class*="lang-"], [class*="lang-"] code');
            for (var i = 0, element; element = elements[i++]; ) {
                _.highlightElement(element, async === true, callback);
            }
        },highlightElement: function(element, async, callback) {
            var language, grammar, parent = element;
            while (parent && !lang.test(parent.className)) {
                parent = parent.parentNode;
            }
            if (parent) {
                language = (parent.className.match(lang) || [, ''])[1];
                grammar = _.languages[language];
            }
            if (!grammar) {
                return;
            }
            element.className = element.className.replace(lang, '').replace(/\s+/g, ' ') + ' language-' + language;
            parent = element.parentNode;
            if (/pre/i.test(parent.nodeName)) {
                parent.className = parent.className.replace(lang, '').replace(/\s+/g, ' ') + ' language-' + language;
            }
            var code = element.textContent.trim();
            if (!code) {
                return;
            }
            code = code.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\u00a0/g, ' ');
            var env = {element: element,language: language,grammar: grammar,code: code};
            _.hooks.run('before-highlight', env);
            if (async && self.Worker) {
                var worker = new Worker(_.filename);
                worker.onmessage = function(evt) {
                    env.highlightedCode = Token.stringify(JSON.parse(evt.data));
                    env.element.innerHTML = env.highlightedCode;
                    callback && callback.call(env.element);
                    _.hooks.run('after-highlight', env);
                };
                worker.postMessage(JSON.stringify({language: env.language,code: env.code}));
            } 
            else {
                env.highlightedCode = _.highlight(env.code, env.grammar)
                env.element.innerHTML = env.highlightedCode;
                callback && callback.call(element);
                _.hooks.run('after-highlight', env);
            }
        },highlight: function(text, grammar) {
            return Token.stringify(_.tokenize(text, grammar));
        },tokenize: function(text, grammar) {
            var Token = _.Token;
            var strarr = [text];
            var rest = grammar.rest;
            if (rest) {
                for (var token in rest) {
                    grammar[token] = rest[token];
                }
                delete grammar.rest;
            }
            tokenloop: for (var token in grammar) {
                if (!grammar.hasOwnProperty(token) || !grammar[token]) {
                    continue;
                }
                var pattern = grammar[token], inside = pattern.inside, lookbehind = !!pattern.lookbehind || 0;
                pattern = pattern.pattern || pattern;
                for (var i = 0; i < strarr.length; i++) {
                    var str = strarr[i];
                    if (strarr.length > text.length) {
                        break tokenloop;
                    }
                    if (str instanceof Token) {
                        continue;
                    }
                    pattern.lastIndex = 0;
                    var match = pattern.exec(str);
                    if (match) {
                        if (lookbehind) {
                            lookbehind = match[1].length;
                        }
                        var from = match.index - 1 + lookbehind, match = match[0].slice(lookbehind), len = match.length, to = from + len, before = str.slice(0, from + 1), after = str.slice(to + 1);
                        var args = [i, 1];
                        if (before) {
                            args.push(before);
                        }
                        var wrapped = new Token(token, inside ? _.tokenize(match, inside) : match);
                        args.push(wrapped);
                        if (after) {
                            args.push(after);
                        }
                        Array.prototype.splice.apply(strarr, args);
                    }
                }
            }
            return strarr;
        },hooks: {all: {},add: function(name, callback) {
                var hooks = _.hooks.all;
                hooks[name] = hooks[name] || [];
                hooks[name].push(callback);
            },run: function(name, env) {
                var callbacks = _.hooks.all[name];
                if (!callbacks || !callbacks.length) {
                    return;
                }
                for (var i = 0, callback; callback = callbacks[i++]; ) {
                    callback(env);
                }
            }}};
    var Token = _.Token = function(type, content) {
        this.type = type;
        this.content = content;
    };
    Token.stringify = function(o) {
        if (typeof o == 'string') {
            return o;
        }
        if (Object.prototype.toString.call(o) == '[object Array]') {
            for (var i = 0; i < o.length; i++) {
                o[i] = Token.stringify(o[i]);
            }
            return o.join('');
        }
        var env = {type: o.type,content: Token.stringify(o.content),tag: 'span',classes: ['token', o.type],attributes: {}};
        if (env.type == 'comment') {
            env.attributes['spellcheck'] = 'true';
        }
        _.hooks.run('wrap', env);
        var attributes = '';
        for (var name in env.attributes) {
            attributes += name + '="' + (env.attributes[name] || '') + '"';
        }
        return '<' + env.tag + ' class="' + env.classes.join(' ') + '" ' + attributes + '>' + env.content + '</' + env.tag + '>';
    };
    if (!self.document) {
        self.addEventListener('message', function(evt) {
            var message = JSON.parse(evt.data), lang = message.language, code = message.code;
            self.postMessage(JSON.stringify(_.tokenize(code, _.languages[lang])));
            self.close();
        }, false);
        return;
    }
    /*var script = document.getElementsByTagName('script');
    script = script[script.length - 1];
    if (script) {
        _.filename = script.src;
        if (document.addEventListener && !script.hasAttribute('data-manual')) {
            document.addEventListener('DOMContentLoaded', _.highlightAll);
        }
    }*/
    if (document.addEventListener) {
        document.addEventListener('DOMContentLoaded', _.highlightAll);
    }
})();
Prism.languages.markup = {'comment': /&lt;!--[\w\W]*?--(&gt;|&gt;)/g,'prolog': /&lt;\?.+?\?&gt;/,'doctype': /&lt;!DOCTYPE.+?&gt;/,'cdata': /&lt;!\[CDATA\[[\w\W]+?]]&gt;/i,'tag': {pattern: /&lt;\/?[\w:-]+\s*[\w\W]*?&gt;/gi,inside: {'tag': {pattern: /^&lt;\/?[\w:-]+/i,inside: {'punctuation': /^&lt;\/?/,'namespace': /^[\w-]+?:/}},'attr-value': {pattern: /=(('|")[\w\W]*?(\2)|[^\s>]+)/gi,inside: {'punctuation': /=/g}},'punctuation': /\/?&gt;/g,'attr-name': {pattern: /[\w:-]+/g,inside: {'namespace': /^[\w-]+?:/}}}},'entity': /&amp;#?[\da-z]{1,8};/gi};
Prism.hooks.add('wrap', function(env) {
    if (env.type === 'entity') {
        env.attributes['title'] = env.content.replace(/&amp;/, '&');
    }
});
Prism.languages.css = {'comment': /\/\*[\w\W]*?\*\//g,'atrule': /@[\w-]+?(\s+.+)?(?=\s*{|\s*;)/gi,'url': /url\((["']?).*?\1\)/gi,'selector': /[^\{\}\s][^\{\}]*(?=\s*\{)/g,'property': /(\b|\B)[a-z-]+(?=\s*:)/ig,'string': /("|')(\\?.)*?\1/g,'important': /\B!important\b/gi,'ignore': /&(lt|gt|amp);/gi,'punctuation': /[\{\};:]/g};
if (Prism.languages.markup) {
    Prism.languages.insertBefore('markup', 'tag', {'style': {pattern: /(&lt;|<)style[\w\W]*?(>|&gt;)[\w\W]*?(&lt;|<)\/style(>|&gt;)/ig,inside: {'tag': {pattern: /(&lt;|<)style[\w\W]*?(>|&gt;)|(&lt;|<)\/style(>|&gt;)/ig,inside: Prism.languages.markup.tag.inside},rest: Prism.languages.css}}});
}
Prism.languages.javascript = {'comment': {pattern: /(^|[^\\])(\/\*[\w\W]*?\*\/|\/\/.*?(\r?\n|$))/g,lookbehind: true},'string': /("|')(\\?.)*?\1/g,'regex': {pattern: /(^|[^/])\/(?!\/)(\[.+?]|\\.|[^/\r\n])+\/[gim]{0,3}(?=\s*($|[\r\n,.;})]))/g,lookbehind: true},'keyword': /\b(var|let|if|else|while|do|for|return|in|instanceof|function|new|with|typeof|try|catch|finally|null|break|continue)\b/g,'boolean': /\b(true|false)\b/g,'number': /\b-?(0x)?\d*\.?\d+\b/g,'operator': /[-+]{1,2}|!|=?&lt;|=?&gt;|={1,2}|(&amp;){1,2}|\|?\||\?|\*|\//g,'ignore': /&(lt|gt|amp);/gi,'punctuation': /[{}[\];(),.:]/g};
if (Prism.languages.markup) {
    Prism.languages.insertBefore('markup', 'tag', {'script': {pattern: /(&lt;|<)script[\w\W]*?(>|&gt;)[\w\W]*?(&lt;|<)\/script(>|&gt;)/ig,inside: {'tag': {pattern: /(&lt;|<)script[\w\W]*?(>|&gt;)|(&lt;|<)\/script(>|&gt;)/ig,inside: Prism.languages.markup.tag.inside},rest: Prism.languages.javascript}}});
}
Prism.languages.php = {'comment': {pattern: /(^|[^\\])(\/\*[\w\W]*?\*\/|\/\/.*?(\r?\n|$))/g,lookbehind: true},'deliminator': /(\?>|\?&gt;|&lt;\?php|<\?php)/ig,'variable': /(\$\w+)\b/ig,'string': /("|')(\\?.)*?\1/g,'regex': {pattern: /(^|[^/])\/(?!\/)(\[.+?]|\\.|[^/\r\n])+\/[gim]{0,3}(?=\s*($|[\r\n,.;})]))/g,lookbehind: true},'keyword': /\b(and|or|xor|array|as|break|case|cfunction|class|const|continue|declare|default|die|do|else|elseif|enddeclare|endfor|endforeach|endif|endswitch|endwhile|extends|for|foreach|function|include|include_once|global|if|new|return|static|switch|use|require|require_once|var|while|abstract|interface|public|implements|extends|private|protected|throw)\b/g,'function': /\b(abs|acos|acosh|addcslashes|addslashes|array_change_key_case|array_chunk|array_combine|array_count_values|array_diff|array_diff_assoc|array_diff_key|array_diff_uassoc|array_diff_ukey|array_fill|array_filter|array_flip|array_intersect|array_intersect_assoc|array_intersect_key|array_intersect_uassoc|array_intersect_ukey|array_key_exists|array_keys|array_map|array_merge|array_merge_recursive|array_multisort|array_pad|array_pop|array_product|array_push|array_rand|array_reduce|array_reverse|array_search|array_shift|array_slice|array_splice|array_sum|array_udiff|array_udiff_assoc|array_udiff_uassoc|array_uintersect|array_uintersect_assoc|array_uintersect_uassoc|array_unique|array_unshift|array_values|array_walk|array_walk_recursive|atan|atan2|atanh|base64_decode|base64_encode|base_convert|basename|bcadd|bccomp|bcdiv|bcmod|bcmul|bindec|bindtextdomain|bzclose|bzcompress|bzdecompress|bzerrno|bzerror|bzerrstr|bzflush|bzopen|bzread|bzwrite|ceil|chdir|checkdate|checkdnsrr|chgrp|chmod|chop|chown|chr|chroot|chunk_split|class_exists|closedir|closelog|copy|cos|cosh|count|count_chars|date|decbin|dechex|decoct|deg2rad|delete|ebcdic2ascii|echo|empty|end|ereg|ereg_replace|eregi|eregi_replace|error_log|error_reporting|escapeshellarg|escapeshellcmd|eval|exec|exit|exp|explode|extension_loaded|feof|fflush|fgetc|fgetcsv|fgets|fgetss|file_exists|file_get_contents|file_put_contents|fileatime|filectime|filegroup|fileinode|filemtime|fileowner|fileperms|filesize|filetype|floatval|flock|floor|flush|fmod|fnmatch|fopen|fpassthru|fprintf|fputcsv|fputs|fread|fscanf|fseek|fsockopen|fstat|ftell|ftok|getallheaders|getcwd|getdate|getenv|gethostbyaddr|gethostbyname|gethostbynamel|getimagesize|getlastmod|getmxrr|getmygid|getmyinode|getmypid|getmyuid|getopt|getprotobyname|getprotobynumber|getrandmax|getrusage|getservbyname|getservbyport|gettext|gettimeofday|gettype|glob|gmdate|gmmktime|ini_alter|ini_get|ini_get_all|ini_restore|ini_set|interface_exists|intval|ip2long|is_a|is_array|is_bool|is_callable|is_dir|is_double|is_executable|is_file|is_finite|is_float|is_infinite|is_int|is_integer|is_link|is_long|is_nan|is_null|is_numeric|is_object|is_readable|is_real|is_resource|is_scalar|is_soap_fault|is_string|is_subclass_of|is_uploaded_file|is_writable|is_writeable|mkdir|mktime|nl2br|parse_ini_file|parse_str|parse_url|passthru|pathinfo|readlink|realpath|rewind|rewinddir|rmdir|round|str_ireplace|str_pad|str_repeat|str_replace|str_rot13|str_shuffle|str_split|str_word_count|strcasecmp|strchr|strcmp|strcoll|strcspn|strftime|strip_tags|stripcslashes|stripos|stripslashes|stristr|strlen|strnatcasecmp|strnatcmp|strncasecmp|strncmp|strpbrk|strpos|strptime|strrchr|strrev|strripos|strrpos|strspn|strstr|strtok|strtolower|strtotime|strtoupper|strtr|strval|substr|substr_compare)\b/g,'constant': /\b(__FILE__|__LINE__|__METHOD__|__FUNCTION__|__CLASS__)\b/g,'boolean': /\b(true|false)\b/g,'number': /\b-?(0x)?\d*\.?\d+\b/g,'operator': /[-+]{1,2}|!|=?&lt;|=?&gt;|={1,2}|(\&amp;){1,2}|\|?\||\?|\*|\//g,'punctuation': /[{}[\];(),.:]/g};
if (Prism.languages.markup) {
    Prism.languages.insertBefore('php', 'comment', {'markup': {pattern: /(\?>|\?&gt;)[\w\W]*?(?=(&lt;\?php|<\?php))/ig,lookbehind: true,inside: {'markup': {pattern: /&lt;\/?[\w:-]+\s*[\w\W]*?&gt;/gi,inside: Prism.languages.markup.tag.inside},rest: Prism.languages.php}}});
}
</script>
<?
}
}/** all: Prism syntax highlighter (wpjam version) END */


