<#1>
<?php
/** @var $ilDB \ilDBInterface */
if (!$ilDB->tableExists("ui_uihk_agop_history")) {
    $ilDB->createTable("ui_uihk_agop_history", [
        'id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
        ],
        "user_id" => [
            "type" => "integer",
            "length" => 4,
            'notnull' => true,
        ],
        "date" => [
            "type" => "date",
            'notnull' => true,
        ],
        "datasets" => [
            "type" => "integer",
            "length" => 4,
            "notnull" => true,
        ],
    ]);
    $ilDB->addPrimaryKey("ui_uihk_agop_history", ["id"]);
    $ilDB->createSequence("ui_uihk_agop_history");
}

if (!$ilDB->tableExists("ui_uihk_agop_grades")) {
    $ilDB->createTable("ui_uihk_agop_grades", [
        'id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
        ],
        "user_id" => [
            "type" => "integer",
            "length" => 4,
            'notnull' => true,
        ],
        "note_id" => [
            "type" => "integer",
            "length" => 4,
            "notnull" => true,
        ],
        "matrikel" => [
            "type" => "text",
            "length" => 128,
            "notnull" => true,
        ],
        "stg" => [
            "type" => "text",
            "length" => 128,
            "notnull" => true,
        ],
        "subject_number" => [
            "type" => "text",
            "length" => 128,
            "notnull" => true,
        ],
        "subject_short_name" => [
            "type" => "text",
            "length" => 16,
            "notnull" => true,
        ],
        "subject_name" => [
            "type" => "text",
            "length" => 128,
            "notnull" => true,
        ],
        "semester" => [
            "type" => "integer",
            "length" => 4,
            "notnull" => true,
        ],
        "instructor_name" => [
            "type" => "text",
            "length" => 64,
            "notnull" => true,
        ],
        "type" => [
            "type" => "text",
            "length" => 32,
            "notnull" => true,
        ],
        "date" => [
            "type" => "date",
            "notnull" => true,
        ],
        "grade" => [
            "type" => "float",
            "notnull" => true,
        ],
        "evaluation" => [
            "type" => "float",
            "notnull" => true,
        ],
        "average_evaluation" => [
            "type" => "float",
            "notnull" => true,
        ],
        "credits" => [
            "type" => "float",
            "notnull" => true,
        ],
        "seat_number" => [
            "type" => "integer",
            "length" => 4,
            "notnull" => true,
        ],
        "status" => [
            "type" => "text",
            "length" => 64,
            "notnull" => true,
        ],
        "subject_authorization" => [
            "type" => "integer",
            "length" => 1,
            "notnull" => true,
        ],
        "remark" => [
            "type" => "text",
            "length" => 255,
            "notnull" => true,
        ],
        "created_at" => [
            "type" => "date",
            "notnull" => true,
        ],
        "modified_at" => [
            "type" => "date",
            "notnull" => true,
        ],
    ]);
    $ilDB->addPrimaryKey("ui_uihk_agop_grades", ["id"]);
    $ilDB->createSequence("ui_uihk_agop_grades");
}
?>
<#2>
<?php
if ($ilDB->tableExists("ui_uihk_agop_history")) {
    $ilDB->modifyTableColumn("ui_uihk_agop_history", "id", [
        'type' => 'integer',
        'length' => 8,
        'notnull' => true,
    ]);
}

if ($ilDB->tableExists("ui_uihk_agop_grades")) {
    $ilDB->modifyTableColumn("ui_uihk_agop_grades", "id", [
        'type' => 'integer',
        'length' => 8,
        'notnull' => true,
    ]);
}
?>
<#3>
<?php
if ($ilDB->tableExists("ui_uihk_agop_grades")) {
    $ilDB->modifyTableColumn("ui_uihk_agop_grades", "note_id", [
        'type' => 'integer',
        'length' => 8,
        'notnull' => true,
    ]);
}
?>
