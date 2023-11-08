<?php
 session_start();

const HOST = "localhost";
const USER = "fgygvuiz";
const PASSWORD = "eaK57C";
const DATABASE = "fgygvuiz_m3";
$con = mysqli_connect(HOST, USER, PASSWORD,DATABASE);
mysqli_set_charset($con, "utf8");
