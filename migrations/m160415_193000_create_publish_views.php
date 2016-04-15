<?php

use yii\db\Migration;

class m160415_193000_create_publish_views extends Migration
{

    public function up()
    {

        $this->execute("CREATE VIEW `comment_publish` AS
             select `comment`.`id` AS `publish_id`
             from `comment`
             #--is active
             where exists (select 1 from comment_active where active_id=comment.id)
             #--not banned TODO
               #--and `comment`.`approval_status` in (0,1)
             #-- Model is publish
               and (
                    (`comment`.`model` = 'location' and exists (select 1 from `location_publish` where publish_id = `comment`.`model_id`))
                 or (`comment`.`model` = 'artwork' and exists (select 1 from `artwork_publish` where publish_id = `comment`.`model_id`))
                 or (`comment`.`model` = 'event' and exists (select 1 from `event_publish` where publish_id = `comment`.`model_id`))
                 or (`comment`.`model` = 'artist' and exists (select 1 from `artist_publish` where publish_id = `comment`.`model_id`))
                 )
             #-- User is publish
                and exists (select 1 from user_publish where publish_id=comment.user_id);"
        );

    }

    public function down()
    {

        $this->execute("DROP VIEW `comment_publish`");

    }
}
