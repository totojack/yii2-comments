<?php
/**
 * @link http://www.yee-soft.com/
 * @copyright Copyright (c) 2015 Yee CMS
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

namespace yeesoft\comments;

use Yii;

/**
 * Comments Module For Yii2 Framework
 *
 * @author Taras Makitra <makitrataras@gmail.com>
 */
class Comments extends \yii\base\Module
{
    /**
     * Version number of the module.
     */
    const VERSION = '0.2';

    /**
     * Path to default avatar image
     */
    const DEFAULT_AVATAR = '/images/user.png';

    /**
     *  Comments Module controller namespace
     *
     * @var string
     */
    public $controllerNamespace = 'yeesoft\comments\controllers';

    /**
     *  User model class name
     *
     * @var string
     */
    public $userModel = 'common\models\User';

    /**
     * Name to display if user is deleted
     *
     * @var string
     */
    public $deletedUserName = 'DELETED';

    /**
     * Maximum allowed nested level for comment's replies
     *
     * @var int
     */
    public $maxNestedLevel = 5;

    /**
     * Count of first level comments per page
     *
     * @var int
     */
    public $commentsPerPage = 5;

    /**
     *  Indicates whether not registered users can leave a comment
     *
     * @var boolean
     */
    public $onlyRegistered = false;

    /**
     * Comments order direction
     *
     * @var int const
     */
    public $orderDirection = SORT_DESC;

    /**
     * Replies order direction
     *
     * @var int const
     */
    public $nestedOrderDirection = SORT_ASC;

    /**
     * The field for displaying user avatars.
     *
     * Is this field is NULL default avatar image will be displayed. Also it
     * can specify path to image or use callable type.
     *
     * If this property is specified as a callback, it should have the following signature:
     *
     * ~~~
     * function ($user_id)
     * ~~~
     *
     * Example of module settings :
     * ~~~
     * 'comments' => [
     *       'class' => 'yeesoft\comments\Comments',
     *       'userAvatar' => function($user_id){
     *           return User::getUserAvatarByID($user_id);
     *       }
     *   ]
     * ~~~
     * @var string|callable
     */
    public $userAvatar;

    /**
     *
     *  Example of module settings :
     * ~~~
     * 'comments' => [
     *       'class' => 'yeesoft\comments\Comments',
     *       'userUrl' => function($user_id){
     *           return User::getUserUrlByID($user_id);
     *       }
     *   ]
     * @var string|callable
     */
    public $userUrl;

    /**
     *
     *
     * @var boolean
     */
    public $displayAvatar = true;

    /**
     * Comments asset url
     *
     * @var string
     */
    public $commentsAssetUrl;

    /**
     * Pattern that will be applied for user names on comment form.
     *
     * @var string
     */
    public $usernameRegexp = '/^(\w|\d|_|\-| )+$/';

    /**
     * Pattern that will be applied for user names on comment form.
     * It contain regexp that should NOT be in username
     * Default pattern doesn't allow anything having "admin"
     *
     * @var string
     */
    public $usernameBlackRegexp = '/^(.)*admin(.)*$/i';

    /**
     * Comments module ID.
     *
     * @var string
     */
    public $commentsModuleID = 'comments';

    /**
     * Options for captcha
     *
     * @var array
     */
    public $captchaOptions = [
        'class' => 'yii\captcha\CaptchaAction',
        'minLength' => 4,
        'maxLength' => 6,
        'offset' => 5,
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['yii2-comments/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@vendor/totojack/yii2-comments/messages',
            'fileMap' => [
                'yii2-comments/comments' => 'comments.php',
            ],
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('yii2-comments/' . $category, $message, $params, $language);
    }

    /**
     * Render user avatar by UserID according to $userAvatar setting
     *
     * @param int $user_id
     * @return string
     */
    public function renderUserAvatar($user_id)
    {
        $this->userAvatar = self::getInstance()->userAvatar;
        if ($this->userAvatar === null) {
            return $this->commentsAssetUrl . self::DEFAULT_AVATAR;
        } elseif (is_string($this->userAvatar)) {
            return $this->userAvatar;
        } else {
            return call_user_func($this->userAvatar, $user_id);
        }
    }

    public function renderUserUrl($user_id)
    {
        $this->userUrl = self::getInstance()->userUrl;
        if ($this->userUrl === null) {
            return '';
        } elseif (is_string($this->userUrl)) {
            return $this->userUrl;
        } else {
            return call_user_func($this->userUrl, $user_id);
        }
    }
}
