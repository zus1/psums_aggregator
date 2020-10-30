<?php


class StreamRulesModel extends Model
{
    protected $idField = 'id';
    protected $table = 'stream_rules';
    protected $dataSet = array(
        "id", "first_stream", "second_stream", "rule_id"
    );
}