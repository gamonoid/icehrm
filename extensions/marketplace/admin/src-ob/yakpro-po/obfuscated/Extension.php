<?php
/*   __________________________________________________
    |  Obfuscated by YAK Pro - Php Obfuscator  2.0.14  |
    |              on 2026-04-03 23:40:09              |
    |    GitHub: https://github.com/pk-fr/yakpro-po    |
    |__________________________________________________|
*/
/*

*/
 namespace MarketplaceAdmin; use Classes\BaseService; use Classes\IceExtension; use MarketplaceAdmin\Migrations\CreateTables; class Extension extends IceExtension { public function initialize() { } public function setupModuleClassDefinitions() { } public function setupRestEndPoints() { (new ApiController())->registerEndPoints(); } }
