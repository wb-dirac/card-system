<?php
namespace App\Library\Pay\Demo; use App\Library\Pay\ApiInterface; use Illuminate\Support\Facades\Log; class Api implements ApiInterface { private $url_notify = ''; private $url_return = ''; public function __construct($sp53f8aa) { $this->url_notify = SYS_URL_API . '/pay/notify/' . $sp53f8aa; $this->url_return = SYS_URL . '/pay/return/' . $sp53f8aa; } function goPay($spbe80b7, $spa3e681, $sp45f07e, $sp873488, $sp5213ee) { sleep(5); header('Location:' . $this->url_return . '/' . $spa3e681); die; } function verify($spbe80b7, $sp04f0f8) { $sp3bce01 = isset($spbe80b7['isNotify']) && $spbe80b7['isNotify']; if ($sp3bce01) { } else { $sp7c88f3 = @$spbe80b7['out_trade_no']; if (strlen($sp7c88f3) < 5) { throw new \Exception('交易单号未传入'); } $spd63ffb = date('YmdHis'); $sp04f0f8($sp7c88f3, \App\Order::whereOrderNo($sp7c88f3)->first()->paid, $spd63ffb); return true; } return true; } }