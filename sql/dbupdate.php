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
<#4>
<?php
if ($ilDB->tableExists("ui_uihk_agop_grades")) {
    $ilDB->dropTableColumn("ui_uihk_agop_grades", "user_id");
}
?>
<#5>
<?php
if ($ilDB->tableExists("ui_uihk_agop_grades")) {
    $ilDB->addIndex("ui_uihk_agop_grades", ["matrikel"], "i1");
}
?>
<#6>
<?php
if ($ilDB->tableExists("ui_uihk_agop_history")) {
    $ilDB->modifyTableColumn("ui_uihk_agop_history", "date", [
        'type' => 'timestamp',
        'notnull' => true,
    ]);
}
?>
<#7>
<?php
$ilDB->dropTable("ui_uihk_agop_grades", false);
$ilDB->dropTable("ui_uihk_agop_history", false);

$ilDB->createTable("ui_uihk_agop_grades", [
        'id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
        ],
        "fp_id_nr" => [
            "type" => "integer",
            "length" => 8,
            "notnull" => true
        ],
        "tln_id" => [
            "type" => "integer",
            "length" => 8,
            "notnull" => true
        ],
        "tln_name_long" => [
            "type" => "text",
            "length" => 255,
            "notnull" => true
        ],
        "semester" => [
            "type" => "text",
            "length" => 255,
            "notnull" => true
        ],
        "semester_location" => [
            "type" => "text",
            "length" => 255,
            "notnull" => true
        ],
        "date" => [
            "type" => "timestamp",
            "notnull" => true,
        ],
        "subject_name" => [
            "type" => "text",
            "length" => 255,
            "notnull" => true
        ],
        "dozent" => [
            "type" => "text",
            "length" => 255,
            "notnull" => true
        ],
        "grade" => [
            "type" => "float",
            "notnull" => true
        ],
        "ects_pkt_tn" => [
            "type" => "float",
            "notnull" => true
        ],
        "passed" => [
            "type" => "integer",
            "length" => 1,
            "notnull" => true,
            "default" => "0"
        ],
        "error_text" => [
            "type" => "text",
            "length" => 255,
            "notnull" => true
        ],
        "number_of_repeats" => [
            "type" => "integer",
            "length" => 4,
            "notnull" => true
        ],
        "created_at" => [
            "type" => "timestamp",
            "notnull" => true,
        ],
        "modified_at" => [
            "type" => "timestamp",
            "notnull" => true,
        ],
    ]
);

$ilDB->addPrimaryKey("ui_uihk_agop_grades", ["id"]);
$ilDB->createSequence("ui_uihk_agop_grades");
$ilDB->addIndex("ui_uihk_agop_grades", ["fp_id_nr"], "i1");

$ilDB->createTable("ui_uihk_agop_history", [
    'id' => [
        'type' => 'integer',
        'length' => 8,
        'notnull' => true,
    ],
    "user_id" => [
        "type" => "integer",
        "length" => 8,
        'notnull' => true,
    ],
    "date" => [
        "type" => "timestamp",
        'notnull' => true,
    ],
    "datasets_added" => [
        "type" => "integer",
        "length" => 4,
        "notnull" => true,
    ],
    "datasets_changed" => [
        "type" => "integer",
        "length" => 4,
        "notnull" => true,
    ],
    "datasets_unchanged" => [
        "type" => "integer",
        "length" => 4,
        "notnull" => true,
    ],
]);
$ilDB->addPrimaryKey("ui_uihk_agop_history", ["id"]);
$ilDB->createSequence("ui_uihk_agop_history");
?>
<#8>
<?php
if ($ilDB->tableExists("ui_uihk_agop_grades")) {
    $ilDB->modifyTableColumn("ui_uihk_agop_grades", "date", [
        "type" => "timestamp",
        "notnull" => false,
    ]);
}
?>
<#9>
<?php
if ($ilDB->tableExists("ui_uihk_agop_grades")) {
    $ilDB->modifyTableColumn("ui_uihk_agop_grades", "date", [
        "type" => "timestamp",
        "notnull" => true,
        "default" => ""
    ]);
}
?>
<#10>
<?php
if ($ilDB->tableExists("ui_uihk_agop_grades")) {
    $ilDB->dropTableColumn("ui_uihk_agop_grades", "date");
    $ilDB->addTableColumn("ui_uihk_agop_grades", "date",
        [
            "type" => "timestamp",
            "notnull" => true,
        ]
    );
}
?>
<#11>
<?php
if ($ilDB->tableExists("ui_uihk_agop_grades")) {
    $ilDB->renameTableColumn("ui_uihk_agop_grades", "dozent", "tutor");
}
?>
