<?php
if($_POST['newRecordCount']) {
    $recordIdField = $this->getRecordIdField($project_id);
    $metadata = $module->getMetadata();
    $sampleRecord = [];
    foreach($metadata as $field) {
        if($module->isRepeatingForm($field['field_name'])) {
            for($i = 1; $i <= $_POST['repeatInstanceCount'];$i++) {
            }
        }
    }
}
