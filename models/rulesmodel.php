<?php

namespace PsumsAggregator\Models;

class RulesModel extends Model
{
    protected $idField = 'id';
    protected $table = 'rules_available';
    protected $dataSet = array(
        "id", "rule_name", "rule_description", "pattern"
    );
}