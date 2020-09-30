<?php

namespace App\Http\Controllers;

use App\Category;
use App\Library\Helper;
use App\Pay;
use App\Product;
use App\System;
use App\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use App\Library\Geetest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class HomeController extends Controller
{
    private function _shop_render($spc33961, $spd58c4f = null, $sp863814 = null)
    {
        $sp0451dd = array('url' => config('app.url'), 'company' => config('app.company'), 'name' => config('app.name'), 'logo' => config('app.logo'), 'functions' => array());
        if (System::_getInt('product_manual')) {
            $sp0451dd['functions'][] = 'product_manual';
        }
        if (System::_getInt('mail_send_order')) {
            $sp0451dd['functions'][] = 'mail_send_order';
            if (System::_getInt('mail_send_order_use_contact')) {
                $sp0451dd['functions'][] = 'mail_send_order_use_contact';
            }
        }
        if (System::_getInt('sms_send_order')) {
            $sp0451dd['functions'][] = 'sms_send_order';
            $sp0451dd['sms_send_order'] = array('sms_price' => System::_getInt('sms_price'));
        }
        $sp0451dd['captcha'] = array('driver' => System::_get('vcode_driver'), 'config' => array(), 'scene' => array('shop' => array('buy' => System::_getInt('vcode_shop_buy'), 'search' => System::_getInt('vcode_shop_search'))));
        $sp9a876f = Cookie::get('customer');
        $sp02f623 = Cookie::make('customer', strlen($sp9a876f) !== 32 ? md5(str_random(16)) : $sp9a876f, 43200, null, null, false, false);
        $sp573d3e = null;
        if (isset($_GET['theme'])) {
            $sp573d3e = \App\ShopTheme::whereName($_GET['theme'])->first();
        }
        if ($spc33961) {
            $sp0451dd['shop'] = array('name' => config('app.name'), 'qq' => System::_get('shop_qq'), 'ann' => System::_get('shop_ann'), 'ann_pop' => System::_get('shop_ann_pop'), 'inventory' => System::_getInt('shop_inventory'));
            $sp2b9c94 = false;
            if ($sp863814) {
                $spd58c4f->setVisible(array('id', 'name', 'password_open'));
                if ($spd58c4f->password_open) {
                    $spd58c4f->setAttribute('password', $spd58c4f->getTmpPassword());
                    $spd58c4f->addVisible(array('password'));
                }
                $sp863814->setForShop($spc33961);
                $sp0451dd['categories'] = array($spd58c4f);
                $sp0451dd['product'] = $sp863814;
                $sp664a00 = $sp863814->name . ' - ' . $sp0451dd['name'];
                $sp6273b8 = $sp863814->description;
                if (@$sp6273b8[0] === '{') {
                    $sp998ad1 = array();
                    preg_match_all('/"insert":"(.+?)"/', $sp6273b8, $sp998ad1);
                    $sp6273b8 = str_replace('\\n', ' ', @join(' ', $sp998ad1[1]));
                }
            } elseif ($spd58c4f) {
                $spd58c4f->setVisible(array('id', 'name', 'password_open'));
                $sp0451dd['categories'] = array($spd58c4f);
                $sp0451dd['product'] = null;
                $sp664a00 = $spd58c4f->name . ' - ' . $sp0451dd['name'];
                $sp6273b8 = $spd58c4f->name;
            } else {
                $sp8e63a5 = Category::where('user_id', $spc33961->id)->orderBy('sort')->where('enabled', 1)->get();
                foreach ($sp8e63a5 as $spd58c4f) {
                    $spd58c4f->setVisible(array('id', 'name', 'password_open'));
                }
                $sp0451dd['categories'] = $sp8e63a5;
                if (config('app.name') && config('app.title')) {
                    $sp664a00 = config('app.name') . ' - ' . config('app.title');
                } else {
                    $sp664a00 = config('app.name') ? config('app.name') : config('app.title');
                }
                $sp6273b8 = config('app.description');
                $sp2b9c94 = config('app.keywords');
            }
            $sp0451dd['pays'] = \App\PayWay::gets($spc33961, function ($sp8e2ceb) {
                $sp8e2ceb->where('type', \App\PayWay::TYPE_SHOP)->whereRaw('enabled&' . (Helper::is_mobile() ? \App\PayWay::ENABLED_MOBILE : \App\PayWay::ENABLED_PC) . '!=0');
            });
            if (!$sp573d3e) {
                $sp573d3e = \App\ShopTheme::defaultTheme();
            }
            $sp0451dd['theme'] = $spc33961->theme_config && isset($spc33961->theme_config[$sp573d3e->name]) ? $spc33961->theme_config[$sp573d3e->name] : $sp573d3e->config;
            $sp2b9c94 = $sp2b9c94 ? $sp2b9c94 : preg_replace('/[、，；。！？]/', ', ', $sp664a00);
        } else {
            throw new \Exception('不可能到這');
        }
        if (isset($sp0451dd['theme']['background']) && $sp0451dd['theme']['background'] === '內置1') {
            $sp0451dd['theme']['background'] = Helper::b1_rand_background();
        }
        if ($spc33961 && $sp863814 === null) {
            if (@$sp0451dd['theme']['list_type'] === 'list') {
                foreach ($sp0451dd['categories'] as $spb1c766) {
                    if (!$spb1c766->password_open) {
                        $spb1c766->getProductsForShop();
                    }
                }
            } else {
                if (count($sp0451dd['categories']) === 1) {
                    $spb1c766 = $sp0451dd['categories'][0];
                    if (!$spb1c766->password_open) {
                        $spb1c766->getProductsForShop();
                    }
                }
            }
        }
        return response()->view('shop_theme.' . $sp573d3e->name . '.index', array('name' => $sp664a00, 'title' => config('app.title'), 'keywords' => $sp2b9c94, 'description' => $sp6273b8, 'js_tj' => System::_get('js_tj'), 'js_kf' => System::_get('js_kf'), 'config' => $sp0451dd))->cookie($sp02f623);
    }

    public function shop_default(Request $sp13451b)
    {
        $this->checkIsInMaintain();
        $sp8e3b29 = $sp13451b->get('tab', '');
        return response()->redirectTo('/?theme=Material#/record?tab=' . $sp8e3b29);
    }

    private function _shop_404()
    {
        $this->checkIsInMaintain();
        return view('message', array('title' => '404 NotFound', 'message' => '該鏈接不存在<br>
<a style="font-size: 18px" href="/?theme=Material#/record">查詢訂單</a>'));
    }

    public function shop_category($sp046fc6)
    {
        $this->checkIsInMaintain();
        $spd58c4f = Category::whereId(Helper::id_decode($sp046fc6, Helper::ID_TYPE_CATEGORY))->with('user')->first();
        if (!$spd58c4f && is_numeric($spd58c4f)) {
            $spd58c4f = Category::whereId($sp046fc6)->where('created_at', '<', \Carbon\Carbon::createFromDate(2019, 1, 1))->with('user')->first();
        }
        if (!$spd58c4f) {
            return $this->_shop_404();
        }
        return $this->_shop_render($spd58c4f->user, $spd58c4f);
    }

    public function shop_product(Request $sp13451b, $spc63e58)
    {
        $this->checkIsInMaintain();
        $sp863814 = Product::whereId(Helper::id_decode($spc63e58, Helper::ID_TYPE_PRODUCT))->with(array('user', 'category'))->first();
        if (!$sp863814 && is_numeric($spc63e58)) {
            $sp863814 = Product::whereId($spc63e58)->where('created_at', '<', \Carbon\Carbon::createFromDate(2019, 1, 1))->with(array('user', 'category'))->first();
        }
        if (!$sp863814 || !$sp863814->category) {
            return $this->_shop_404();
        }
        if ($sp863814->password_open && $sp863814->password !== $sp13451b->input('p')) {
            return view('message', array('title' => '當前商品需要密碼', 'message' => ($sp13451b->has('p') ? '密碼錯誤，請重新輸入' : '請輸入商品密碼') . '<br>
<style type="text/css">
.content{ width: 100%;}
.password{display: block;margin: 8px auto;font-size: 14px;width: 90%;max-width: 340px;height: 30px;border: 1px solid #666; outline: none;padding: 0 10px;vertical-align: top;border-radius: 0;}
.confirm-btn{display: inline-block;width: 136px;height: 35px;line-height: 35px;border: none;text-align: center;font-size: 14px;text-decoration: none;cursor: pointer;margin-bottom: 15px;}
</style>
<div style="font-size: 14px">
<input id="password" type="password" class="password">
<button class="confirm-btn" onclick="location.href=location.href.split(\'?\')[0]+\'?p=\'+encodeURI(document.getElementById(\'password\').value)" style="">確認</button>
</div><br>
<a style="font-size: 18px" href="/s#/record">查詢訂單</a>&nbsp;&nbsp;
<a style="font-size: 18px" href="/s#/report">投訴訂單</a>\'
'));
        }
        return $this->_shop_render($sp863814->user, $sp863814->category, $sp863814);
    }

    public function shop()
    {
        $this->checkIsInMaintain();
        $spc33961 = User::firstOrFail();
        return $this->_shop_render($spc33961);
    }

    public function admin()
    {
        $sp0451dd = array();
        $sp0451dd['url'] = config('app.url');
        $sp0451dd['captcha'] = array('driver' => System::_get('vcode_driver'), 'config' => array(), 'scene' => array('auth' => array('login' => System::_getInt('vcode_login_admin'))));
        if (System::_getInt('product_manual')) {
            $sp0451dd['functions'] = array('product_manual');
        }
        return view('admin', array('config' => $sp0451dd));
    }
}