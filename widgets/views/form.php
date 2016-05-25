<?php

    use yeesoft\comments\assets\CommentsAsset;
    use yeesoft\comments\Comments;
    use yii\helpers\Html;
    use yii\helpers\Url;
    use yii\widgets\ActiveForm;

    /* @var $this yii\web\View */
    /* @var $modelyeesoft\comments\models\Comment */
?>

<?php
    $commentsAsset = CommentsAsset::register($this);
    Comments::getInstance()->commentsAssetUrl = $commentsAsset->baseUrl;

    $formID = 'comment-form' . (($comment->parent_id) ? '-' . $comment->parent_id : '');
    $replyClass = ($comment->parent_id) ? 'comment-form-reply' : '';
?>

<div class="comment-form <?=$replyClass;?> clearfix">

    <?php
        $form = ActiveForm::begin([
            'action' => null,
            'validateOnBlur' => false,
            'validationUrl' => Url::to(['/' . Comments::getInstance()->commentsModuleID . '/validate/index']),
            'id' => $formID,
            'class' => 'com-form',
        ]);

        if ($comment->parent_id) {
            echo $form->field($comment, 'parent_id')->hiddenInput()->label(false);
        }
    ?>
<?php if (Comments::getInstance()->displayAvatar): ?>
        <div class="avatar">
            <a href="<?=Comments::getInstance()->renderUserUrl(Yii::$app->user->id);?>">
                <img src="<?=Comments::getInstance()->renderUserAvatar(Yii::$app->user->id);?>"/>
            </a>
        </div>
    <?php endif;?>
    <div class="comment-fields<?=(Comments::getInstance()->displayAvatar) ? ' display-avatar' : '';?>">

        <div class="row">
            <div class="col-lg-12">
                <?php echo
                    $form->field($comment, 'content')->textarea([
                        'class' => 'form-control input-sm',
                        'placeholder' => Comments::t('comments', 'Share your thoughts...'),
                    ])->label(false);
                ?>
            </div>
        </div>

        <div class="comment-fields-more">
            <div class="buttons text-right">
                <?=Html::button(Comments::t('comments', 'Cancel'), ['class' => 'btn btn-default btn-sm reply-cancel rounded']);?>
                <?=Html::submitButton(($comment->parent_id) ? Comments::t('comments', 'Reply') : Comments::t('comments', 'Post'), ['class' => 'btn btn-primary btn-sm rounded']);?>
            </div>
            <div class="fields">
                <div class="row">
                    <?php if (Yii::$app->user->isGuest): ?>
                        <div class="col-lg-6">
                            <?php echo
                                $form->field($comment, 'username', ['enableAjaxValidation' => true])->textInput([
                                    'maxlength' => true,
                                    'class' => 'form-control input-sm',
                                    'placeholder' => Comments::t('comments', 'Your name'),
                                ])->label(false)
                            ; ?>
                        </div>
                        <div class="col-lg-6">
                            <?php echo
                                $form->field($comment, 'email')->textInput([
                                    'maxlength' => true,
                                    'email' => true,
                                    'class' => 'form-control input-sm',
                                    'placeholder' => Comments::t('comments', 'Your email'),
                                ])->label(false)
                            ; ?>
                        </div>
                    <?php else: ?>
                        <!--div class="col-lg-6">
                            <?=(($comment->parent_id) ? Comments::t('comments', 'Reply as') : Comments::t('comments', 'Post as')) . ' <b>' . Yii::$app->user->identity->username . '</b>';?>
                        </div-->
                    <?php endif;?>
                </div>
            </div>
        </div>

    </div>

    <?php ActiveForm::end();?>

</div>


<?php if (false) {}
    ; //if (Yii::$app->getRequest()->post()) {; //$options    = Json::htmlEncode($form->getClientOptions());; //$attributes = Json::htmlEncode($form->attributes);; //\yii\widgets\ActiveFormAsset::register($this);; //$this->registerJs("jQuery('#$formID').yiiActiveForm($attributes, $options);");; //}
?>


