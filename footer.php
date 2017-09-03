</section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->
		<script type="text/javascript">


		
		for (var prop in modJsList) {
			if(modJsList.hasOwnProperty(prop)){
				modJsList[prop].setTranslations(<?=\Classes\LanguageManager::getTranslations()?>);
				modJsList[prop].setPermissions(<?=json_encode($modulePermissions['perm'])?>);
				modJsList[prop].setFieldTemplates(<?=json_encode($fieldTemplates)?>);
				modJsList[prop].setTemplates(<?=json_encode($templates)?>);
				modJsList[prop].setCustomTemplates(<?=json_encode($customTemplates)?>);
				<?php if(isset($emailTemplates)){?>
				modJsList[prop].setEmailTemplates(<?=json_encode($emailTemplates)?>);
				<?php } ?>
				modJsList[prop].setUser(<?=json_encode($user)?>);
                <?php if(isset($_REQUEST['action']) && $_REQUEST['action'] == "new"){?>
                if(modJsList[prop].newInitObject == undefined || modJsList[prop].newInitObject == null){
                    modJsList[prop].initFieldMasterData(null,modJsList[prop].renderForm);
                }else{
                    modJsList[prop].initFieldMasterData(null,modJsList[prop].renderForm, modJsList[prop].newInitObject);
                }
                <?php }else{ ?>
				if(modJsList[prop].initialFilter != null && modJsList[prop].initialFilter != undefined){
					modJsList[prop].initFieldMasterData(null,modJsList[prop].setFilterExternal);
				}else{
					modJsList[prop].initFieldMasterData();
				}

                <?php } ?>
				modJsList[prop].setBaseUrl('<?=BASE_URL?>');
				modJsList[prop].setCurrentProfile(<?=json_encode($activeProfile)?>);
				modJsList[prop].setInstanceId('<?=\Classes\BaseService::getInstance()->getInstanceId()?>');
				modJsList[prop].setGoogleAnalytics(ga);
				modJsList[prop].setNoJSONRequests('<?=\Classes\SettingsManager::getInstance()->getSetting("System: Do not pass JSON in request")?>');
				
			}
			
	    }


		//Other static js objects
		
		var timeUtils = new TimeUtils();
		timeUtils.setServerGMToffset('<?=$diffHoursBetweenServerTimezoneWithGMT?>');
		
		var notificationManager = new NotificationManager();
		notificationManager.setBaseUrl('<?=CLIENT_BASE_URL?>service.php');
		notificationManager.setTimeUtils(timeUtils);
		
		<?php 
			$notificationTemplates = array();
			$notificationTemplates['notification'] = file_get_contents(BASE_URL."/templates/notifications/notification.html");
			$notificationTemplates['notifications'] = file_get_contents(BASE_URL."/templates/notifications/notifications.html");
		?>
		notificationManager.setTemplates(<?=json_encode($notificationTemplates)?>);
		
		//-----------------------
	   

		$(document).ready(function() {
			$('#modTab a').click(function (e) {
                if($(this).hasClass('dropdown-toggle')){
                    return;
                }
				e.preventDefault();
				$(this).tab('show');
				modJs = modJsList[$(this).attr('id')];
				modJs.get([]);
                modJs.initFieldMasterData();
				var helpLink = modJs.getHelpLink();
				if(helpLink != null && helpLink != undefined){
					$('.helpLink').attr('href',helpLink);
					$('.helpLink').show();
				}else{
					$('.helpLink').hide();
				}

			});

			var tabName = window.location.hash.substr(1);

			if(tabName!= undefined && tabName != "" && modJsList[tabName] != undefined && modJsList[tabName] != null){
				$("#"+tabName).click();	
			}else{
                <?php if(!isset($_REQUEST['action']) && $_REQUEST['action'] != "new"){?>
				modJs.get([]);
                <?php } ?>
			}
			

			notificationManager.getNotifications();

			$("#delegationDiv").on('click', "#notifications", function(e) {
				$(this).find('.label-danger').remove();
				notificationManager.clearPendingNotifications();
				
			});

			$("#switch_emp").select2();

			var helpLink = modJs.getHelpLink();
			if(helpLink != null && helpLink != undefined){
				$('.helpLink').attr('href',helpLink);
				$('.helpLink').show();
			}else{
				$('.helpLink').hide();
			}
			
		});
		var clientUrl = '<?=CLIENT_BASE_URL?>';

        var modulesInstalled = <?=json_encode(\Classes\BaseService::getInstance()->getModuleManagerNames())?>;

		$(document).ready(function() {

			$(".dataTables_paginate ul").addClass("pagination");
			
			var refId = "";
			<?php if(empty($_REQUEST['m'])){?>
				<?php if($user->user_level == 'Admin'){?>
					refId = '<?="admin_".str_replace(" ", "_", $adminModules[0]['name'])?>';
					$("[ref = '"+refId+"'] a").first().click();
				<?php }else{?>
					refId = '<?="module_".str_replace(" ", "_", $userModules[0]['name'])?>';
					$("[ref = '"+refId+"'] a").first().click();
				<?php }?>
			<?php } else{?>
				refId = '<?=$_REQUEST['m']?>';
				$("[ref = '"+refId+"'] a").first().click();
			<?php }?>

			<?php if(!isset($proVersion) && isset($moduleName) && $moduleName == 'dashboard' && $user->user_level == 'Admin' && !\Classes\BaseService::getInstance()->validateInstance()){?>
			$("#verifyModel").modal({
				  backdrop: 'static'
			});
			<?php }?>

		});


	</script>
	<?php include 'popups.php';?>
	<?php include APP_BASE_PATH.'js/bootstrapDataTable.php';?>
    </body>
</html>
