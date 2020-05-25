<?php
function get_basedir() :string {
  return realpath(dirname(__FILE__) . '../../');
}

function get_customdir() :string {
  return get_basedir() . '/custom/';
}

?>