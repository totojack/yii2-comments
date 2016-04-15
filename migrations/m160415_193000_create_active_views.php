<?php

use yii\db\Migration;

class m160415_193000_create_active_views extends Migration
{

    public $tablename = 'comment';

    public function up()
    {
        $this->execute("CREATE VIEW `comment_active` AS
         select `comment`.`id` AS `active_id`
         from `comment`
         where `comment`.`is_deleted` = 0
            -- Model
        and (
            (`comment`.`model` = 'location' and exists (select 1 from `location_active` where active_id = `comment`.`model_id`))
         or (`comment`.`model` = 'artwork' and exists (select 1 from `artwork_active` where active_id = `comment`.`model_id`))
         or (`comment`.`model` = 'event' and exists (select 1 from `event_active` where active_id = `comment`.`model_id`))
         or (`comment`.`model` = 'artist' and exists (select 1 from `artist_active` where active_id = `comment`.`model_id`))
         )
        -- User
        and exists (select 1 from user_active where active_id=comment.user_id);"
        );
    }

    public function down()
    {
        $this->execute("DROP VIEW `comment_active`");
    }
}
