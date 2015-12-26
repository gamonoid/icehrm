<?php
/*
This file is part of iCE Hrm.

iCE Hrm is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

iCE Hrm is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with iCE Hrm. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]  
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */

$moduleName = 'dashboard';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">
			  
	<div class="row">
		<div class="col-lg-3 col-xs-12">
			<!-- small box -->
			<div class="small-box bg-aqua">
				<div class="inner">
					<h3>
					People
					</h3>
					<p id="numberOfEmployees">
					.. Employees
					</p>
				</div>
				<div class="icon">
					<i class="ion ion-person-stalker"></i>
				</div>
				<a href="#" class="small-box-footer" id="employeeLink">
					Manage Employees <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div><!-- ./col -->						                        
		<div class="col-lg-3 col-xs-12">
			<!-- small box -->
			<div class="small-box bg-green">
				<div class="inner">
					<h3 id="numberOfCompanyStuctures">..</h3>
					<p >
					Company Structures
					</p>
				</div>
				<div class="icon">
					<i class="ion ion-shuffle"></i>
				</div>
				<a href="#" class="small-box-footer" id="companyLink">
					Manage Company <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div><!-- ./col -->
		<div class="col-lg-3 col-xs-12">
			<!-- small box -->
			<div class="small-box bg-yellow">
				<div class="inner">
					<h3>Users</h3>
					<p id="numberOfUsers">
						.. Users
					</p>
				</div>
				<div class="icon">
					<i class="ion ion-person-add"></i>
				</div>
				<a href="#" class="small-box-footer" id="usersLink">
					Manage Users <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div><!-- ./col -->
		<div class="col-lg-3 col-xs-12">
			<!-- small box -->
			<div class="small-box bg-red">
				<div class="inner">
					<h3 id="numberOfProjects">..</h3>
					<p>
						Active Projects
					</p>
				</div>
				<div class="icon">
					<i class="ion ion-pie-graph"></i>
				</div>
				<a href="#" class="small-box-footer" id="projectsLink">
					Update Clients/Projects <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div><!-- ./col -->

		<div class="col-lg-3 col-xs-12">
			<!-- small box -->
			<div class="small-box bg-yellow">
				<div class="inner">
					<h3>
					Attendance
					</h3>
					<p id="numberOfAttendanceLastWeek">
					.. Entries Last Week
					</p>
				</div>
				<div class="icon">
					<i class="ion ion-clock"></i>
				</div>
				<a href="#" class="small-box-footer" id="attendanceLink">
					Monitor Attendance <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div><!-- ./col -->
		<div class="col-lg-3 col-xs-12">
			<!-- small box -->
			<div class="small-box bg-teal">
				<div class="inner">
					<h3>Reports</h3>
					<p>
						View / Download Reports
					</p>
				</div>
				<div class="icon">
					<i class="ion ion-document-text"></i>
				</div>
				<a href="#" class="small-box-footer" id="reportsLink">
					Create a Report <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div><!-- ./col -->
		<div class="col-lg-3 col-xs-12">
			<!-- small box -->
			<div class="small-box bg-green">
				<div class="inner">
					<h3>Settings</h3>
					<p>
						Configure IceHrm
					</p>
				</div>
				<div class="icon">
					<i class="ion ion-settings"></i>
				</div>
				<a href="#" class="small-box-footer" id="settingsLink">
					Update Settings <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div><!-- ./col -->
        <div class="col-lg-3 col-xs-12">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>
                        Travel
                    </h3>
                    <p id="numberOfTravel">
                        Requests
                    </p>
                </div>
                <div class="icon">
                    <i class="ion ion-plane"></i>
                </div>
                <a href="#" class="small-box-footer" id="travelLink">
                    Manage Travel <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-12">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>
                        Help
                    </h3>
                    <p>
                        User Guide
                    </p>
                </div>
                <div class="icon">
                    <i class="ion ion-help"></i>
                </div>
                <a href="http://blog.icehrm.com/docs/home/" target="_blank" class="small-box-footer" id="icehrmHelpLink">
                    Documentation <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-12">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>
                        Purchase
                    </h3>
                    <p>
                        Additional Modules
                    </p>
                </div>
                <div class="icon">
                    <i class="ion ion-ios-cart"></i>
                </div>
                <a href="http://icehrm.com/modules.php" target="_blank" class="small-box-footer">
                    Documentation <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
	</div>

    <div id="iceannon">
        <div class="callout callout-danger lead" style="font-size: 14px;font-weight: bold;">
            <h4>Why not upgrade to IceHrm Pro Version</h4>
            <p>
                IceHrm Pro is the feature rich upgrade to IceHrm open source version. It comes with improved modules for
                employee management, leave management and number of other features over open source version.
                Hit this <a href="http://icehrm.com/#compare" class="btn btn-primary btn-xs target="_blank">link</a> to do a full one to one comparison.

                Also you can learn more about IceHrm Pro <a href="http://blog.icehrm.com/docs/icehrm-pro/" class="btn btn-primary btn-xs" target="_blank">here</a>
                <br/>
                <br/>
                <a href="http://icehrm.com/modules.php" class="btn btn-success btm-xs" target="_blank"><i class="fa fa-checkout"></i> Buy IceHrm Pro</a>
            </p>
        </div>
    </div>

	

</div>
<script>
var modJsList = new Array();

modJsList['tabDashboard'] = new DashboardAdapter('Dashboard','Dashboard');

var modJs = modJsList['tabDashboard'];

$("#employeeLink").attr("href",modJs.getCustomUrl('?g=admin&n=employees&m=admin_Admin'));
$("#companyLink").attr("href",modJs.getCustomUrl('?g=admin&n=company_structure&m=admin_Admin'));
$("#usersLink").attr("href",modJs.getCustomUrl('?g=admin&n=users&m=admin_System'));
$("#projectsLink").attr("href",modJs.getCustomUrl('?g=admin&n=projects&m=admin_Admin'));
$("#attendanceLink").attr("href",modJs.getCustomUrl('?g=admin&n=attendance&m=admin_Admin'));
$("#leaveLink").attr("href",modJs.getCustomUrl('?g=admin&n=leaves&m=admin_Admin'));
$("#reportsLink").attr("href",modJs.getCustomUrl('?g=admin&n=reports&m=admin_Reports'));
$("#settingsLink").attr("href",modJs.getCustomUrl('?g=admin&n=settings&m=admin_System'));
$("#travelLink").attr("href",modJs.getCustomUrl('?g=admin&n=travel&m=admin_Employees'));


modJs.getInitData();

$(document).ready(function(){
    try{
        $.ajax({
            url : "https://icehrm-public.s3.amazonaws.com/icehrmnews.html",
            success : function(result){
                $('#iceannon').html(result);
            }
        });
    }catch(e){}

});

</script>
<?php include APP_BASE_PATH.'footer.php';?>      