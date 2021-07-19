<?php
/*$user = \Classes\BaseService::getInstance()->getCurrentUser();
echo "Welcome ".$user->username."<br/>";

echo "Invoices <br/>";
*/
use Classes\PermissionManager;
use Invoices\Model\Invoice;

?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabInvoices" href="#tabPageInvoices"><?=t('Invoices')?></a></li>
	</ul>

    <div class="tab-content">
		<div class="tab-pane active" id="tabPageInvoices">
			<div id="InvoicesTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="InvoicesForm"></div>
            <div id="InvoicesFilterForm"></div>
		</div>
    </div>

</div>
<div id="dataGroup"></div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'Invoice' => PermissionManager::checkGeneralAccess(new Invoice()),
    ]
];
?>

<script>
  initAdminInvoices(<?=json_encode($moduleData)?>);
</script>

