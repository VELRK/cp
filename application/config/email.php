<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Outbound mail. SMTP is used when SMTP_HOST is set in .env; otherwise PHP mail().
 * Hostinger: SMTP_HOST=smtp.hostinger.com, SMTP_PORT=465, SMTP_CRYPTO=ssl,
 * SMTP_USER and SMTP_PASS = a mailbox on the domain. NB_MAIL_FROM should match that mailbox.
 */
$smtp_host = getenv('SMTP_HOST');
if (($smtp_host === false || $smtp_host === '') && !empty($_SERVER['SMTP_HOST'])) {
    $smtp_host = (string) $_SERVER['SMTP_HOST'];
}
$smtp_host = is_string($smtp_host) ? trim($smtp_host) : '';

$smtp_user = getenv('SMTP_USER');
if (($smtp_user === false || $smtp_user === '') && !empty($_SERVER['SMTP_USER'])) {
    $smtp_user = (string) $_SERVER['SMTP_USER'];
}
$smtp_pass = getenv('SMTP_PASS');
if (($smtp_pass === false || $smtp_pass === '') && isset($_SERVER['SMTP_PASS'])) {
    $smtp_pass = (string) $_SERVER['SMTP_PASS'];
}
$smtp_port = getenv('SMTP_PORT');
if (($smtp_port === false || $smtp_port === '') && !empty($_SERVER['SMTP_PORT'])) {
    $smtp_port = (string) $_SERVER['SMTP_PORT'];
}
$smtp_crypto = getenv('SMTP_CRYPTO');
if (($smtp_crypto === false || $smtp_crypto === '') && !empty($_SERVER['SMTP_CRYPTO'])) {
    $smtp_crypto = (string) $_SERVER['SMTP_CRYPTO'];
}

$config['useragent'] = 'Coimbatore Properties';
$config['mailtype'] = 'html';
$config['charset'] = 'utf-8';
$config['wordwrap'] = true;
$config['newline'] = "\r\n";
$config['crlf'] = "\r\n";
$config['validate'] = false;

if ($smtp_host !== '') {
    $config['protocol'] = 'smtp';
    $config['smtp_host'] = $smtp_host;
    $config['smtp_user'] = is_string($smtp_user) ? $smtp_user : '';
    $config['smtp_pass'] = is_string($smtp_pass) ? $smtp_pass : '';
    $config['smtp_port'] = $smtp_port !== '' && $smtp_port !== false ? (int) $smtp_port : 465;
    $crypto = is_string($smtp_crypto) ? strtolower(trim($smtp_crypto)) : 'ssl';
    $config['smtp_crypto'] = in_array($crypto, array('ssl', 'tls'), true) ? $crypto : 'ssl';
} else {
    $config['protocol'] = 'mail';
}
