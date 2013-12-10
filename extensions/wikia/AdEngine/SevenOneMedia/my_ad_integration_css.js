/* exported SEVENONEMEDIA_CSS */
/* jshint quotmark:false, maxlen:false*/

/**
 * This is a JavaScript file encapsulating the my_ad_integration.css file we got from SevenOne Media.
 * It ads the <style> tag with CSS rules found in my_ad_integration.css to the <head> using
 * document.head.insertAdjacentHTML.
 *
 * If the my_ad_integration.css changes, put it to devbox and then run the following command:
 *
 *   curl http://rychu.wikia-dev.com/__am/$RANDOM/one/-/extensions/wikia/AdEngine/SevenOneMedia/my_ad_integration.css 2>/dev/null | php -r 'echo "var SEVENONEMEDIA_CSS = " . json_encode(file_get_contents("php://stdin")) . ";" . PHP_EOL;'
 *
 * Replace rychu in the above command with your devbox name. Paste the result after this comment.
 *
 * The command gets the minified CSS and exports it to JavaScript as var SEVENONEMEDIA_CSS using
 * PHP's json_encode to encode the value safely for JavaScript.
 */

var SEVENONEMEDIA_CSS = ".ad-version:after{content:'$Revision: 1.6 $';display:none}.ad-wrapper div,.ad-wrapper object,.ad-wrapper embed,.ad-wrapper img,.ad-wrapper iframe,.ad-wrapper ins{vertical-align:bottom}#ads-outer{width:1030px;margin:0px auto;position:relative;z-index:30}#ad-fullbanner2-outer{position:relative;z-index:2}#ad-fullbanner2{min-height:90px;margin:0px 0px 0px auto;padding:10px 0px}#ad-skyscraper1-outer{position:absolute;z-index:1;right:-170px;width:160px}#ad-skyscraper1{width:160px}#ad-rectangle1{width:300px;min-height:263px;margin:0px auto;padding:15px}#ad-promo1-outer,#ad-promo2-outer,#ad-promo3-outer{float:left}#ad-promo1,#ad-promo2,#ad-promo3{width:300px;margin:0 15px}#ad-rectangle1:before,#ad-promo1:before,#ad-promo2:before,#ad-promo3:before{content:'Anzeige';display:block;font-size:11px;line-height:13px;text-align:right;color:#3a3a3a}#HOME_TOP_RIGHT_BOXAD,#TOP_RIGHT_BOXAD{margin:0px !important;padding:0px !important;width:auto;height:auto;position:relative;z-index:101}#ads-outer #TOP_BUTTON_WIDE{position:absolute;z-index:3;max-width:292px;margin:10px 0px 0px 0px;padding:0px;float:none}";