<?php
session_start();
session_destroy();

$location = "Location: https://nextgen.jansoftworks.fi";
header($location);