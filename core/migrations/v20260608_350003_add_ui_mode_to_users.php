<?php
namespace Classes\Migration;

class v20260608_350003_add_ui_mode_to_users extends AbstractMigration {

    public function up(){
        // Per-user interface preference: 'new' (React SPA) or 'legacy'. NULL means
        // the user has never chosen — login defaults them to the legacy UI and
        // records 'legacy' so the choice persists across sessions.
        $columns = $this->executeQuery("SHOW COLUMNS FROM `Users` LIKE 'ui_mode'");
        if (empty($columns)) {
            $this->executeQuery("ALTER TABLE Users ADD COLUMN `ui_mode` varchar(16) DEFAULT NULL");
        }

        return true;
    }

    public function down(){
        $this->executeQuery("ALTER TABLE Users DROP COLUMN `ui_mode`");

        return true;
    }

}
