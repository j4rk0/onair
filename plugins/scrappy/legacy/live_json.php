<?php
if(!$_GET['debug']) header('Content-Type: application/json; charset=utf-8');
define('SEE_QUERY', TRUE);

function apply_filters(){};
function mbstring_binary_safe_encoding(){};
function reset_mbstring_encoding(){};
function get_locale(){};
require_once getcwd().'/../../../../wp-includes/formatting.php';
require_once getcwd().'/../api/scrappyAPI.php';
include('live.php');
$radios = radios_reduce_array($radios, array("expres", "slovensko", "fun", "jemne", "europa2", "regina", "vlna", "fm", "antena-rock", "lumen"));

echo json_encode( lastfm_encapsulation($radios) );
?>