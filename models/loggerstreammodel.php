<?php


class LoggerStreamModel extends Model
{
    protected $idField = 'id';
    protected $table = 'log_stream';
    protected $dataSet = array(
        "id", "message", "code", "line", "trace", "file", "created_at", "type"
    );
}