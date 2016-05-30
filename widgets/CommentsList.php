<?php

namespace yeesoft\comments\widgets;

use yeesoft\comments\Comments as CommentsModule;
use yeesoft\comments\models\Comment;
use yii\data\ActiveDataProvider;

/**
 * Widget for displaying comments
 */
class CommentsList extends \yii\base\Widget
{
    public $comment;
    public $model;
    public $model_id;
    public $parent_id;
    public $nested_level = 1;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $orderDirection = ($this->parent_id) ? CommentsModule::getInstance()->nestedOrderDirection : CommentsModule::getInstance()->orderDirection;
        $pageSize = ($this->parent_id) ? 0 : CommentsModule::getInstance()->commentsPerPage;

        $dataProvider = new ActiveDataProvider([
//'query' => UserProfile::find()->joinWith(['user(relation_name)'])->where(['<>','user.status',0])
            'query' => Comment::find()
                ->joinWith('user', '`user`.`id` = `comment`.`user_id`')
                ->where([
                    'comment.model' => $this->model,
                    'comment.model_id' => $this->model_id,
                    'comment.parent_id' => $this->parent_id,
                    'comment.approval_status' => Comment::STATUS_PUBLISHED,
                ]),
            'pagination' => [
                'pageSize' => $pageSize,
                'pageParam' => 'comment-page',
                'pageSizeParam' => 'comments-per-page',
            ],
            'sort' => [
                'defaultOrder' => [
                    'comment.id' => $orderDirection,
                ],
            ],
        ]);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'comment' => $this->comment,
            'nested_level' => $this->nested_level,
        ]);
    }
}
