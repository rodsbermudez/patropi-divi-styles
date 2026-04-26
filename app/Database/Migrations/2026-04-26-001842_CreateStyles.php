<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStyles extends Migration
{
    public function up()
    {
        // Check if table already exists via direct query
        $result = $this->db->query("SHOW TABLES LIKE 'styles'")->getRow();
        if ($result) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'label' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'tipo' => [
                'type' => 'ENUM',
                'constraint' => ['button', 'heading', 'text', 'paragraph', 'menu_item', 'container', 'section'],
            ],
            'element_info' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'styles' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('styles');
    }

    public function down()
    {
        $this->forge->dropTable('styles');
    }
}