<?php
   // Pomocná funkce pro QR Platbu
   function getQRPlatba($iban, $amount, $currency = 'CZK', $message = '') {
       $iban = str_replace(' ', '', $iban);
       $bankCode = substr($iban, 4, 4);
       $accountNumber = ltrim(substr($iban, 8), '0');
       $params = [
           'accountNumber' => $accountNumber,
           'bankCode' => $bankCode,
           'amount' => number_format((float)$amount, 2, '.', ''),
           'currency' => $currency,
           'message' => mb_substr($message, 0, 60),
           'branding' => 'false',
           'size' => 250
       ];
       return "https://api.paylibo.com/paylibo/generator/czech/image?" . http_build_query($params);
   }
