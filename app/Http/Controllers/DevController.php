<?php
namespace App\Http\Controllers; use App\System; use Illuminate\Http\Request; use Illuminate\Support\Facades\Log; use Illuminate\Support\Facades\Mail; class DevController extends Controller { private function check_readable_r($sp5198e2) { if (is_dir($sp5198e2)) { if (is_readable($sp5198e2)) { $spb295de = scandir($sp5198e2); foreach ($spb295de as $spc7294d) { if ($spc7294d != '.' && $spc7294d != '..') { if (!self::check_readable_r($sp5198e2 . '/' . $spc7294d)) { return false; } else { continue; } } } echo $sp5198e2 . '   ...... <span style="color: green">R</span><br>'; return true; } else { echo $sp5198e2 . '   ...... <span style="color: red">R</span><br>'; return false; } } else { if (file_exists($sp5198e2)) { return is_readable($sp5198e2); } } echo $sp5198e2 . '   ...... 文件不存在<br>'; return false; } private function check_writable_r($sp5198e2) { if (is_dir($sp5198e2)) { if (is_writable($sp5198e2)) { $spb295de = scandir($sp5198e2); foreach ($spb295de as $spc7294d) { if ($spc7294d != '.' && $spc7294d != '..') { if (!self::check_writable_r($sp5198e2 . '/' . $spc7294d)) { return false; } else { continue; } } } echo $sp5198e2 . '   ...... <span style="color: green">W</span><br>'; return true; } else { echo $sp5198e2 . '   ...... <span style="color: red">W</span><br>'; return false; } } else { if (file_exists($sp5198e2)) { return is_writable($sp5198e2); } } echo $sp5198e2 . '   ...... 文件不存在<br>'; return false; } private function checkPathPermission($sp61aa55) { self::check_readable_r($sp61aa55); self::check_writable_r($sp61aa55); } public function install() { $sp325bc5 = array(); @ob_start(); self::checkPathPermission(base_path('storage')); self::checkPathPermission(base_path('bootstrap/cache')); $sp325bc5['permission'] = @ob_get_clean(); return view('install', array('var' => $sp325bc5)); } public function test(Request $spfeab54) { } }