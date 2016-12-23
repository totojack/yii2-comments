<?php

    use common\widgets\ReportAbuseWidget;
    use yeesoft\comments\Comments;
    use yeesoft\comments\components\CommentsHelper;
    use yeesoft\comments\widgets\CommentsForm;
    use yeesoft\comments\widgets\CommentsList;
    use yii\helpers\ArrayHelper;
    use yii\helpers\HtmlPurifier;
    use yii\timeago\TimeAgo;

?>
<?php if (Comments::getInstance()->displayAvatar): ?>
    <div class="avatar">
        <?php if ($model->user[0]->is_artist) {?>
        <a href="<?=Comments::getInstance()->renderUserUrl($model->user_id);?>"><img src="<?=Comments::getInstance()->renderUserAvatar($model->user_id);?>"/></a>
        <?php } else {?>
            <img src="<?=Comments::getInstance()->renderUserAvatar($model->user_id);?>"/>
        <?php }?>
    </div>
<?php endif;?>
<div class="comment-content<?=(Comments::getInstance()->displayAvatar) ? ' display-avatar' : '';?>">
    <div class="comment-header">
        <a class="author"><?=HtmlPurifier::process($model->getAuthor());?></a>
        <a class="author">
        <?php if ($model->user[0]->is_artist) {
                echo '<i class="fa fa-graduation-cap orange"></i>&nbsp;';
            }

            if ($model->user[0]->is_owner) {
                echo '<i class="fa fa-map-marker cyan"></i>';
            }

        ?>
        </a>
        <span class="time dot-left dot-right"><?=TimeAgo::widget(['timestamp' => $model->created_at, 'language' => substr(\Yii::$app->language, 0, 2)]); //SIMONE ?></span>
    </div>
    <div class="comment-text">
        <?php if ($model->user[0]->approval_status == 5) {?>
        <?=Comments::t('comments', 'User Banned');?>
<?php } else {?>
        <?=HtmlPurifier::process($model->content);?>
<?php }?>
    </div>
    <?php if ($nested_level < Comments::getInstance()->maxNestedLevel): ?>
        <div class="comment-footer">
            <?php if (!Comments::getInstance()->onlyRegistered || !Yii::$app->user->isGuest): ?>
                <a class="reply-button" data-reply-to="<?=$model->id;?>"
                   href="#"><?=Comments::t('comments', 'Reply');?></a>
                <?=ReportAbuseWidget::widget(['abuseUrl' => \Yii::$app->request->url, 'commentId' => $model->id]);?>

                <!--<span class="dot-left"></span>
                <a class="glyphicon glyphicon-thumbs-up"></a> <span>0</span> &nbsp;
                <a class="glyphicon glyphicon-thumbs-down"></a> <span>0</span><span class="dot-left"></span>
                -->
            <?php endif;?>
        </div>
        <?php else: ?>
        <div class="comment-footer">
            <?php if (!Comments::getInstance()->onlyRegistered || !Yii::$app->user->isGuest): ?>
                <?=ReportAbuseWidget::widget(['abuseUrl' => \Yii::$app->request->url, 'commentId' => $model->id]);?>
<?php endif;?>
        </div>
    <?php endif;?>
</div>

<?php if ($nested_level < Comments::getInstance()->maxNestedLevel): ?>
<?php if (!Comments::getInstance()->onlyRegistered || !Yii::$app->user->isGuest): ?>
        <div class="reply-form">
            <?php if ($model->id == ArrayHelper::getValue(Yii::$app->getRequest()->post(), 'Comment.parent_id')): ?>
                <?=CommentsForm::widget(['reply_to' => $model->id]);?>
<?php endif;?>
        </div>
    <?php endif;?>

    <?php
        if ($model->isReplied()) {
            echo CommentsList::widget(ArrayHelper::merge(
                CommentsHelper::getReplyConfig($model), [
                    "comment" => $comment,
                    "nested_level" => $nested_level + 1,
                ]));
        }
    ?>
<?php endif;?>



