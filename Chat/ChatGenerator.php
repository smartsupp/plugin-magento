<?php
namespace Smartsupp;

/**
 * Generates widget chat code for Smartsupp.com
 *
 * PHP version >=5.3
 *
 * @package    Smartsupp
 * @author     Marek Gach <gach@kurzor.net>
 * @copyright  since 2015 SmartSupp.com
 * @version    Git: $Id$
 * @link       https://github.com/smartsupp/chat-code-generator
 * @since      File available since Release 0.1
 */
class ChatGenerator
{
    /**
     * @var array Values which are allowed for ratingType param.
     */
    protected $allowed_rating_types = array('advanced', 'simple');

    /**
     * @var array Values which are allowed for alignX param.
     */
    protected $allowed_align_x = array('right', 'left');

    /**
     * @var array Values which are allowed for alignY param.
     */
    protected $allowed_align_y = array('side', 'bottom');

    /**
     * @var array Values which are allowed for widget param.
     */
    protected $allowed_widget = array('button', 'widget');

    /**
     * @var null|string Your unique chat code. Can be obtained after registration.
     */
    protected $key = null;

    /**
     * @var null|string By default chat conversation is terminated when visitor opens a sub-domain on your website.
     */
    protected $cookie_domain = null;

    /**
     * @var string Chat language.
     */
    protected $language = 'en';

    /**
     * @var string Chat charset defaults to utf-8.
     */
    protected $charset = 'utf-8';

    /**
     * @var null|string Email (basic information).
     */
    protected $email = null;

    /**
     * @var null|string Customer name (basic information).
     */
    protected $name = null;

    /**
     * @var null|array contain additional user information.
     */
    protected $variables = null;

    /**
     * @var bool When the visitor ends the conversation a confirmation window is displayed. This flag defaults to true
     * and can be changed.
     */
    protected $send_email_transcript = true;

    /**
     * @var bool Indicate if rating is enabled.
     */
    protected $rating_enabled = false;

    /**
     * @var string Rating type.
     */
    protected $rating_type = 'simple';

    /**
     * @var bool Set if rating comment is enambled.
     */
    protected $rating_comment = false;

    /**
     * @var string Chat X align.
     */
    protected $align_x = null;

    /**
     * @var string Chat Y align.
     */
    protected $align_y = null;

    /**
     * @var int Chat X offset.
     */
    protected $offset_x = null;

    /**
     * @var int Chat Y offset.
     */
    protected $offset_y = null;

    /**
     * @var string Widget type.
     */
    protected $widget = null;

    /**
     * @var null|string Google analytics key value.
     */
    protected $ga_key = null;

    /**
     * @var null|array Google analytics additional options.
     */
    protected $ga_options = null;

    /**
     * @var bool
     */
    protected $hide_widget = false;

    public function __construct($key = null)
    {
        $this->key = $key;
    }

    /**
     * Set chat language. Also is checking if language is one of allowed values.
     *
     * @param string $language
     * @throws \Exception when parameter value is incorrect
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Set the charset. Check also if charset is allowed and valid value.
     *
     * @param string $charset
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * Allows to set Smartsupp code.
     *
     * @param string $key Smartsupp chat key.
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Smartsupp visitor is identified by unique key stored in cookies. By default chat conversation is terminated when
     * visitor opens a sub-domain on your website. You should set main domain as cookie domain if you want chat
     * conversations uninterrupted across your sub-domains. Insert the cookieDomain parameter in your chat code on main
     * domain and all sub-domains where you want the chat conversation uninterrupted.
     *
     * Example: Use value '.your-domain.com' to let chat work also on all sub-domains and main domain.
     *
     * @param string $cookie_domain
     */
    public function setCookieDomain($cookie_domain)
    {
        $this->cookie_domain = $cookie_domain;
    }

    /**
     * When the visitor ends the conversation a confirmation window is displayed. In this window there is by default a
     * button to send a transcript of the chat conversation to email. You can choose not to show this button.
     */
    public function disableSendEmailTranscript()
    {
        $this->send_email_transcript = false;
    }

    /**
     * After visitors ends a chat conversation, he is prompted to rate the conversation. Rating is disabled by default.
     * Together with enabling it you can set additional parameters.
     *
     * @param string $rating_type
     * @param boolean|false $rating_comment
     * @throws \Exception when parameter value is incorrect
     */
    public function enableRating($rating_type = 'simple', $rating_comment = false)
    {
        if (!in_array($rating_type, $this->allowed_rating_types)) {
            throw new \Exception("Rating type $rating_type is not allowed value. You can use only one of values: " .
                implode(', ', $this->allowed_rating_types) . ".");
        }

        $rating_comment = (bool) $rating_comment;

        $this->rating_enabled = true;
        $this->rating_type = $rating_type;
        $this->rating_comment = $rating_comment;
    }

    /**
     * You can send visitor name. So your visitors won't be anonymous and your chat agents will see info about every
     * visitor, enabling agents to better focus on VIP visitors and provide customized answers.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * You can send visitor email. So your visitors won't be anonymous and your chat agents will see info about every
     * visitor, enabling agents to better focus on VIP visitors and provide customized answers.
     *
     * @param string $name
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Will add additional parameter into Extra user info variables list.
     *
     * @param $id Parameter ID.
     * @param $label Parameter label.
     * @param $value Parameter value.
     */
    public function setVariable($id, $label, $value)
    {
        $variable = array('id' => $id, 'label' => $label, 'value' => $value);

        $this->variables[] = $variable;
    }

    /**
     * By default the chat box is displayed in bottom right corner of the website. You can change the default position
     * along the bottom line or place the chat on right or left side of the website.
     *
     * @param string $align_x Align to right or left.
     * @param string $align_y Align to bottom or side.
     * @param int $offset_x Offset from left or right.
     * @param int $offset_y Offset from top.
     * @throws \Exception When params are not correct.
     */
    public function setAlign($align_x = 'right', $align_y = 'bottom', $offset_x = 10, $offset_y = 100)
    {
        if (!in_array($align_x, $this->allowed_align_x)) {
            throw new \Exception("AllignX value $align_x is not allowed value. You can use only one of values: " .
                implode(', ', $this->allowed_align_x) . ".");
        }

        if (!in_array($align_y, $this->allowed_align_y)) {
            throw new \Exception("AllignY value $align_y is not allowed value. You can use only one of values: " .
                implode(', ', $this->allowed_align_y) . ".");
        }

        $this->align_x = $align_x;
        $this->align_y = $align_y;
        $this->offset_x = $offset_x;
        $this->offset_y = $offset_y;
    }

    /**
     * We supports two chat-box layouts, widget and button. By default is activated layout widget.
     *
     * @param string $widget Parameter value.
     * @throws \Exception when parameter value is incorrect
     */
    public function setWidget($widget = 'widget')
    {
        if (!in_array($widget, $this->allowed_widget)) {
            throw new \Exception("Widget value $widget is not allowed value. You can use only one of values: " .
                implode(', ', $this->allowed_widget) . ".");
        }

        $this->widget = $widget;
    }

    /**
     * Smartsupp is linked with your Google Analytics (GA) automatically. Smartsupp automatically checks your site's
     * code for GA property ID and sends data to that account. If you are using Google Tag Manager (GTM) or you don't
     * have GA code directly inserted in your site's code for some reason, you have to link your GA account as described
     * here.
     * If you have sub-domains on your website and you are tracking all sub-domains in one GA account, use the gaOptions
     * parameter. You can find more info about gaOptions in Google Analytics documentation
     * (@see https://developers.google.com/analytics/devguides/collection/analyticsjs/advanced#customizeTracker).
     *
     * @param $ga_key Google analytics key.
     * @param array|null $ga_options Additional gaOptions.
     */
    public function setGoogleAnalytics($ga_key, Array $ga_options = null)
    {
        $this->ga_key = $ga_key;
        $this->ga_options = $ga_options;
    }

    /**
     * You can hide chat box on certain pages by setting this variable.
     */
    public function hideWidget()
    {
        $this->hide_widget = true;
    }

    /**
     * Function for javascript variable value escaping.
     *
     * @param $str string String to encode.
     * @return string Encoded string.
     */
    public function javascriptEscape($str)
    {
        $new_str = '';

        for ($i = 0; $i < mb_strlen($str); $i++) {
            // obtain single character
            $char = mb_substr($str, $i, 1);

            // if is alphanumeric put directly into string
            if (!in_array($char, array("'"))) {
                $new_str .= $char;
            } else { // else encode as hex
                $new_str .= '\\x' . bin2hex($char);
            }
        }

        return $new_str;
    }

    /**
     * Will assemble chat JS code. Class property with name key need to be set before rendering.
     *
     * @param bool|false $print_out Force to echo JS chat code instead of returning it.
     * @return string
     * @throws \Exception Will reach exception when key param is not set.
     */
    public function render($print_out = false)
    {
        if (empty($this->key)) {
            throw new \Exception("At least KEY param must be set!");
        }

        $params = array();
        $params2 = array();

        // set cookie domain if not blank
        if ($this->cookie_domain) {
            $params[] = "_smartsupp.cookieDomain = '%cookie_domain%';";
        }

        // If is set to false, turn off. Default value is true.
        if (!$this->send_email_transcript) {
            $params[] = "_smartsupp.sendEmailTanscript = false;";
        }

        if ($this->rating_enabled) {
            $params[] = "_smartsupp.ratingEnabled = true;  // by default false";
            $params[] = "_smartsupp.ratingType = '" . $this->rating_type . "'; // by default 'simple'";
            $params[] = "_smartsupp.ratingComment = " . ($this->rating_comment? 'true':'false') . ";  // default false";
        }

        if ($this->align_x && $this->align_y && $this->widget) {
            $params[] = "_smartsupp.alignX = '" . $this->align_x . "'; // or 'left'";
            $params[] = "_smartsupp.alignY = '" . $this->align_y . "';  // by default 'bottom'";
            $params[] = "_smartsupp.widget = '" . $this->widget . "'; // by default 'widget'";
        }

        if ($this->offset_x && $this->offset_y) {
            $params[] = "_smartsupp.offsetX = " . (int)$this->offset_x . ";    // offset from left / right, default 10";
            $params[] = "_smartsupp.offsetY = " . (int)$this->offset_y . ";    // offset from top, default 100";
        }

        // set detailed visitor's info
        // basic info
        if ($this->email) {
            $params2[] = "smartsupp('email', '" . self::javascriptEscape($this->email) . "');";
        }

        if ($this->name) {
            $params2[] = "smartsupp('name', '" . self::javascriptEscape($this->name) . "');";
        }


        // extra info
        if ($this->variables) {
            $options = array();

            foreach ($this->variables as $key => $value) {
                $options[] = self::javascriptEscape($value['id']) .": {label: '" .
                    self::javascriptEscape($value['label']) . "', value: '" . self::javascriptEscape($value['value']) .
                    "'}";
            }

            $params2[] = "smartsupp('variables', {" .
                implode(", ", $options) .
            "});";
        }


        // set GA key and additional GA params
        if ($this->ga_key) {
            $params[] = "_smartsupp.gaKey = '%ga_key%';";

            if (!empty($this->ga_options)) {
                $options = array();

                foreach ($this->ga_options as $key => $value) {
                    $options[] = "'" . self::javascriptEscape($key) . "': '" . self::javascriptEscape($value) . "'";
                }

                $params[] = "_smartsupp.gaOptions = {" . implode(", ", $options) . "};";
            }
        }

        // hide widget if needed
        if ($this->hide_widget) {
            $params[] = "_smartsupp.hideWidget = true;";
        }

        // create basic code and append params
        $code = "<script type=\"text/javascript\">
            var _smartsupp = _smartsupp || {};
            _smartsupp.key = '%key%';\n" .
            implode("\n", $params) . "\n" .
            "window.smartsupp||(function(d) {
                var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
                s=d.getElementsByTagName('script')[0];c=d.createElement('script');
                c.type='text/javascript';c.charset='utf-8';c.async=true;
                c.src='//www.smartsuppchat.com/loader.js';s.parentNode.insertBefore(c,s);
            })(document);"
            . implode("\n", $params2) . "
            </script>";

        $code = str_replace('%key%', self::javascriptEscape($this->key), $code);
        $code = str_replace('%cookie_domain%', self::javascriptEscape($this->cookie_domain), $code);
        $code = str_replace('%ga_key%', self::javascriptEscape($this->ga_key), $code);


        if ($print_out) {
            echo $code;
        } else {
            return $code;
        }
    }
}
