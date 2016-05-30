<?php

namespace yeesoft\comments\models;

use common\components\QueueNotifier;
use yeesoft\comments\Comments;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "comment".
 *
 * @property integer $id
 * @property string $model
 * @property integer $model_id
 * @property integer $user_id
 * @property string $username
 * @property string $email
 * @property integer $parent_id
 * @property integer $approval_status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $content
 * @property string $user_ip
 */

/**
 * Description of Comment
 *
 * @author User
 */
class Comment extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_SPAM = 2;
    const STATUS_TRASH = 3;
    const STATUS_PUBLISHED = self::STATUS_APPROVED;
    const SCENARIO_GUEST = 'guest';
    const SCENARIO_USER = 'user';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'setUserData']);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'user_id',
            ],

        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content'], 'required'],
            [['username', 'email'], 'required', 'on' => self::SCENARIO_GUEST],
            [['created_at', 'approval_status', 'parent_id'], 'integer'],
            [['content'], 'string'],
            [['username'], 'string', 'max' => 128],
            [['username', 'content'], 'string', 'min' => 4],
            ['username', 'match', 'pattern' => Comments::getInstance()->usernameRegexp, 'on' => self::SCENARIO_GUEST],
            ['username', 'match', 'not' => true, 'pattern' => Comments::getInstance()->usernameBlackRegexp, 'on' => self::SCENARIO_GUEST],
            [['email'], 'email'],
            ['username', 'unique',
                'targetClass' => Comments::getInstance()->userModel,
                'targetAttribute' => 'username',
                'on' => self::SCENARIO_GUEST,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_USER] = ['content', 'parent_id'];
        $scenarios[self::SCENARIO_GUEST] = ['username', 'email', 'content', 'parent_id'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Comments::t('comments', 'ID'),
            'model' => Comments::t('comments', 'Model'),
            'model_id' => Comments::t('comments', 'Model ID'),
            'user_id' => Comments::t('comments', 'User ID'),
            'username' => Comments::t('comments', 'Username'),
            'email' => Comments::t('comments', 'E-mail'),
            'parent_id' => Comments::t('comments', 'Parent Comment'),
            'approval_status' => Comments::t('comments', 'Status'),
            'created_at' => Comments::t('comments', 'Created'),
            'updated_at' => Comments::t('comments', 'Updated'),
            'content' => Comments::t('comments', 'Content'),
            'user_ip' => Comments::t('comments', 'IP'),
        ];
    }

    /**
     * @inheritdoc
     *
     * @return CommentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CommentQuery(get_called_class());
    }

    /**
     * getTypeList
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_PENDING => Comments::t('comments', 'Pending'),
            self::STATUS_APPROVED => Comments::t('comments', 'Approved'),
            self::STATUS_SPAM => Comments::t('comments', 'Spam'),
            self::STATUS_TRASH => Comments::t('comments', 'Trash'),
        ];
    }

    /**
     * getStatusOptionsList
     * @return array
     */
    public static function getStatusOptionsList()
    {
        return [
            [self::STATUS_PENDING, Comments::t('comments', 'Pending'), 'default'],
            [self::STATUS_APPROVED, Comments::t('comments', 'Approved'), 'primary'],
            [self::STATUS_SPAM, Comments::t('comments', 'Spam'), 'default'],
            [self::STATUS_TRASH, Comments::t('comments', 'Trash'), 'default'],
        ];
    }

    /**
     * Get created date
     *
     * @param string $format date format
     * @return string
     */
    public function getCreatedDate($format = 'Y-m-d')
    {
        return date($format, ($this->isNewRecord) ? time() : $this->created_at);
    }

    /**
     * Get created date
     *
     * @param string $format date format
     * @return string
     */
    public function getUpdatedDate($format = 'Y-m-d')
    {
        return date($format, ($this->isNewRecord) ? time() : $this->updated_at);
    }

    /**
     * Get created time
     *
     * @param string $format time format
     * @return string
     */
    public function getCreatedTime($format = 'H:i')
    {
        return date($format, ($this->isNewRecord) ? time() : $this->created_at);
    }

    /**
     * Get created time
     *
     * @param string $format time format
     * @return string
     */
    public function getUpdatedTime($format = 'H:i')
    {
        return date($format, ($this->isNewRecord) ? time() : $this->updated_at);
    }

    /**
     * Get author of comment
     *
     * @return string
     */
    public function getAuthor()
    {
        if ($this->user_id) {
            $userModel = Comments::getInstance()->userModel;
            $user = $userModel::findIdentity($this->user_id);
            return ($user && isset($user)) ? $user->username : Comments::getInstance()->deletedUserName;

        } else {
            return $this->username;
        }
    }

    /**
     * Updates user's data before comment insert
     */
    public function setUserData()
    {
        $this->user_ip = Yii::$app->getRequest()->getUserIP();

        if (!Yii::$app->user->isGuest) {
            $this->user_id = Yii::$app->user->id;
        }
    }

    /**
     * Check whether comment has replies
     *
     * @return int nubmer of replies
     */
    public function isReplied()
    {
        return Comment::find()->where(['parent_id' => $this->id])->active()->count();
    }

    /**
     * Get count of active comments by $model and $model_id
     *
     * @param string $model
     * @param int $model_id
     * @return int
     */
    public static function activeCount($model, $model_id = null)
    {
        return Comment::find()->where(['model' => $model, 'model_id' => $model_id])->active()->count();
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            try {
                $queueNotifier = new QueueNotifier();
                $queueNotifier->pushComment($this->getAttributes());
            } catch (Exception $e) {
                \Yii::error("error comment aftersave notification - " . $e->getMessage());
            }

        }
        return parent::afterSave($insert, $changedAttributes);
    }

    public function getActiveView()
    {
        return $this->hasOne(get_called_class() . 'Active', ['active_id' => 'id']);
    }

    /**
     * Utility per verificare se un model è active
     */
    public function isActive()
    {
        return $this->getActiveView()->exists();
    }

    //getter
    public function getIsactive()
    {
        return $this->isActive();
    }

    public function getPublishView()
    {
        return $this->hasOne(get_called_class() . 'Publish', ['publish_id' => 'id']);
    }

    /**
     * Utility per verificare se un model è publish
     */
    public function isPublish()
    {
        return $this->getPublishView()->exists();
    }

    //getter
    public function getIspublish()
    {
        return $this->isPublish();
    }

    public function getUser()
    {
        return $this->hasMany(\common\models\User::className(), ['id' => 'user_id']);
    }
}
