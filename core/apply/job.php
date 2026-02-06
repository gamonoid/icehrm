<?php
require __DIR__.'/includes/functions.php';
if (!empty($job->companyName)) {
    $companyName = $job->companyName;
}
$additional_fields = extract_additional_fields($job->additional_fields);
$closingDate = $job->closingDate ? date('Y-m-d', strtotime($job->closingDate)) : '';
$imageUrl = $job->attachment;
$clientUrl = CLIENT_BASE_URL;
if (substr($clientUrl, -4) !== 'app/') {
    $clientUrl = str_replace('/app/', '/api/', $clientUrl);
    $formAction = $clientUrl.'api/index.php?method=post&url=/jobs/apply';
} else {
    $formAction = $clientUrl.'api/index.php?method=post&url=/jobs/apply';
}
$slashFix = IS_CLOUD ? '' : '/';
?><!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title><?=$job->title.":".$companyName?></title>
  <link rel="shortcut icon" href="<?=BASE_URL.'apply/'?>image/favicon.ico" type="image/x-icon">
  <!-- Bootstrap , fonts & icons  -->
  <link rel="stylesheet" href="<?=BASE_URL.'apply/'?>css/bootstrap.css">
  <!-- Vendor stylesheets  -->
  <link rel="stylesheet" href="<?=BASE_URL.'apply/'?>css/main.css">
  <!-- Custom stylesheet -->
  <link rel="stylesheet" href="<?=BASE_URL.'apply/'?>plugins/nice-select/nice-select.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
</head>

<body>
<script type="text/javascript">
  $(document).ready(function () {

    $("#btnSubmit").click(function (event) {
      $("#error-msg").hide();
      //stop submit the form, we will post it manually.
      event.preventDefault();

      // Get form
      var form = $('#apply-form')[0];

      // Create an FormData object
      var data = new FormData(form);

      // disabled the submit button
      $("#btnSubmit").prop("disabled", true);

      $.ajax({
        type: "POST",
        enctype: 'multipart/form-data',
        url: '<?=$formAction?>',
        data: data,
        processData: false,
        contentType: false,
        cache: false,
        timeout: 600000,
        success: function (data) {
          $("#success-msg").show();
          $('#apply-form').hide();
          $("#btnSubmit").prop("disabled", false);

        },
        error: function (e) {
          var msg = 'Error occurred while sending the application';
          try {
            msg = e.responseJSON.error[0][0].message;
          } catch (d){}


          $("#error-msg").text(msg);
          $("#error-msg").show();
          $("#btnSubmit").prop("disabled", false);
        }
      });

    });

  });
</script>
  <div class="site-wrapper overflow-hidden ">
    <!-- Header Area -->
    <!-- navbar- -->
    <!-- jobDetails-section -->
    <div class="jobDetails-section bg-default-1 pt-10 pb-12">
      <div class="container">
        <div class="row justify-content-center">
          <!-- back Button -->
          <div class="col-xl-10 col-lg-11 mt-4 ml-xxl-32 ml-xl-15 dark-mode-texts">
            <div class="mb-9">
              <a class="d-flex align-items-center ml-4" href="<?=CLIENT_BASE_URL.'apply'.$slashFix?>"> <i
              class="icon icon-small-left bg-white circle-40 mr-5 font-size-4 text-black font-weight-bold shadow-8">
            <</i><span class="text-uppercase font-size-3 font-weight-bold text-gray">Back to job board</span></a>
            </div>
          </div>
          <!-- back Button End -->
          <div class="col-xl-9 col-lg-11 mb-8 px-xxl-15 px-xl-0">
            <div class="bg-white rounded-4 border border-mercury shadow-9">
              <!-- Single Featured Job -->
              <div class="pt-9 pl-sm-9 pl-5 pr-sm-9 pr-5 pb-8 border-bottom border-width-1 border-default-color light-mode-texts">
                <div class="row">
                  <div class="col-md-9">
                    <!-- media start -->
                    <div class="media align-items-center">
                      <!-- media logo start -->
                      <div class="d-block mr-8">
                        <img src="<?=$logoFileUrl?>" alt="" style="max-height: 70px;">
                      </div>
                      <!-- media logo end -->
                      <!-- media texts start -->
                      <div>
                        <h3 class="font-size-6 mb-0"><?=$job->title?></h3>
                        <span class="font-size-3 text-gray line-height-2"><?=$companyName?></span>
                      </div>
                      <!-- media texts end -->
                    </div>
                    <!-- media end -->
                  </div>
                  <div class="col-md-3 text-right pt-7 pt-md-0 mt-md-n1">
                    <!-- media date start -->
                    <div class="media justify-content-md-end">
                      <p class="font-size-4 text-gray mb-0"><?=$closingDate?></p>
                    </div>
                    <!-- media date end -->
                  </div>
                </div>
              </div>
              <!-- End Single Featured Job -->
              <div class="job-details-content pt-8 pl-sm-9 pl-6 pr-sm-9 pr-6 pb-10 border-bottom border-width-1 border-default-color light-mode-texts">
                <div class="row mb-7">
                  <?php if ($job->showSalary === 'Yes') { ?>
                  <div class="col-md-4 pr-lg-0 pl-lg-10 mb-md-0 mb-6">
                    <div class="media justify-content-md-start">
                      <div class="image mr-5">
                          🏦
                      </div>
                      <p class="font-weight-semibold font-size-5 text-black-2 mb-0"><?=$job->salaryMin.'-'.$job->salaryMax.' '.$currency->code?></p>
                    </div>
                  </div>
                    <?php } ?>
                  <div class="col-md-4 pr-lg-0 pl-lg-10 mb-md-0 mb-6">
                    <div class="media justify-content-md-start">
                      <div class="image mr-5">
                          ⏳
                      </div>
                      <p class="font-weight-semibold font-size-5 text-black-2 mb-0"><?=$enrichedJob->$employementTypeName?></p>
                    </div>
                  </div>
                  <div class="col-md-4 pl-lg-0">
                    <div class="media justify-content-md-start">
                      <div class="image mr-5">
                          📍
                      </div>
                      <p class="font-size-5 text-gray mb-0"><?=$job->location ? $job->location : $enrichedJob->country_Name?></p>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4 pr-lg-0 pl-lg-10 mb-lg-0 mb-10">
                    <div class="">
                      <span class="font-size-4 d-block mb-4 text-gray">Career Level</span>
                      <h6 class="font-size-5 text-black-2 font-weight-semibold mb-9"><?=$enrichedJob->$experienceLevelName?></h6>
                    </div>
                      <div class="tags">
                          <p class="font-size-4 text-gray mb-3">Benefits</p>
                          <ul class="d-flex list-unstyled flex-wrap pr-sm-25 pr-md-0">
                              <?php foreach($benifits as $benefit) {?>
                                  <li class="bg-regent-opacity-15 mr-3 h-px-33 text-center flex-all-center rounded-3 px-5 font-size-3 text-black-2 mt-2">
                                      <?=$benefit?>
                                  </li>
                              <?php } ?>
                          </ul>
                      </div>
                  </div>
                  <div class="col-md-4 pr-lg-0 pl-lg-10 mb-lg-0 mb-8">
                    <div class="">
                      <span class="font-size-4 d-block mb-4 text-gray">Job Category</span>
                      <h6 class="font-size-5 text-black-2 font-weight-semibold mb-9"><?=$enrichedJob->$jobFunctionName?></h6>
                    </div>
                  </div>
                  <div class="col-md-4 pl-lg-0">
                    <div class="">
                      <span class="font-size-4 d-block mb-4 text-gray">Education level</span>
                      <h6 class="font-size-5 text-black-2 font-weight-semibold mb-0"><?=$enrichedJob->$educationLevelName?></h6>
                    </div>
                  </div>
                </div>
              </div>
              <div class="job-details-content pt-8 pl-sm-9 pl-6 pr-sm-9 pr-6 pb-10 light-mode-texts">
                <div class="row">
                  <div class="col-xl-11 col-md-12 pr-xxl-9 pr-xl-10 pr-lg-20">
                      <?php if (!empty($imageUrl)) { ?>
                      <div class="">
                          <img src="<?=$imageUrl?>"/>
                          <br/>
                          <br/>
                      </div>
                      <?php } ?>
                    <div class="">
                      <p class="mb-4 font-size-4 font-weight-semibold text-black-2 mb-7">Job Description</p>
                      <p class="font-size-4 text-black-2 mb-7"><?=$job->description?></p>
                    </div>
                    <div class="">
                      <span class="font-size-4 font-weight-semibold text-black-2 mb-7">Your Role:</span>
                      <p class="font-size-4 text-black-2 mb-7"><?=$job->requirements?></p>
                    </div>
                  </div>
                </div>
              </div>
                <div id="apply" class="bg-white px-9 pt-9 pb-7 shadow-8 rounded-4">
                    <div id="success-msg" class="alert alert-success" role="alert" style="display: none;">
                        Thank you. We have received your application.
                    </div>
                    <div id="error-msg" class="alert alert-danger" role="alert" style="display: none;">

                    </div>
                    <form id="apply-form" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <input id="job_id" name="job_id" type="hidden" value="<?=$job->id?>"/>
                            <div class="col-12 mb-7">
                                <label for="" class="font-size-4 font-weight-semibold text-black-2 mb-5 line-height-reset">First Name</label>
                                <input id="first_name" name="first_name" type="text" class="form-control" placeholder="Jhon">
                            </div>
                            <div class="col-12 mb-7">
                                <label for="" class="font-size-4 font-weight-semibold text-black-2 mb-5 line-height-reset">Last Name</label>
                                <input id="last_name" name="last_name" type="text" class="form-control" placeholder="Doe">
                            </div>
                            <div class="col-lg-6 mb-7">
                                <label for="" class="font-size-4 font-weight-semibold text-black-2 mb-5 line-height-reset">E-mail</label>
                                <input id="email" name="email" type="text" class="form-control" placeholder="example@gmail.com">
                            </div>
                            <div class="col-lg-6 mb-7">
                                <label for="" class="font-size-4 font-weight-semibold text-black-2 mb-5 line-height-reset">Telephone</label>
                                <input id="phone" name="phone" type="text" class="form-control" placeholder="+49 123 ...">
                            </div>
                            <div class="col-12 mb-7">
                                <label for="cv" class="font-size-4 font-weight-semibold text-black-2 mb-5 line-height-reset">CV</label>
                                <input id="cv" name="cv" type="file" class="form-control"
                                       accept=".pdf,.doc,.docx" style="height: auto;">
                            </div>
                            <div class="col-lg-12 mb-7">
                                <label for="message" class="font-size-4 font-weight-semibold text-black-2 mb-5 line-height-reset">Cover Letter</label>
                                <textarea id="cover_letter" name="cover_letter" placeholder="Type your message" class="form-control h-px-144"></textarea>
                            </div>
<!--                            <div class="col-12 mb-7">-->
<!--                                <label for="" class="font-size-4 font-weight-semibold text-black-2 mb-5 line-height-reset">Sample Text</label>-->
<!--                                <input id="Custom_Sample_Text" name="Custom_Sample_Text" type="text" class="form-control" placeholder="">-->
<!--                            </div>-->
<!--                            <div class="col-12 mb-7">-->
<!--                                <label for="" class="font-size-4 font-weight-semibold text-black-2 mb-5 line-height-reset">Sample Select</label>-->
<!--                                <select id="Custom_Sample_Select" name="Custom_Sample_Select" type="select" class="form-control">-->
<!--                                    <option value="volvo">Volvo</option>-->
<!--                                    <option value="saab">Saab</option>-->
<!--                                    <option value="mercedes">Mercedes</option>-->
<!--                                    <option value="audi">Audi</option>-->
<!--                                </select>-->
<!--                            </div>-->
                            <?php
                            foreach ($additional_fields as $field) {
								echo create_field($field);
                            }
                            ?>
                            <div class="col-lg-12 pt-4">
                                <button id="btnSubmit" type="submit" class="btn btn-primary text-uppercase w-100 h-px-48">Apply Now</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- footer area function start -->
    <!-- cta section -->
    <footer class="footer bg-ebony-clay dark-mode-texts">
      <div class="container">
        <!-- Cta section -->
        <div class="pt-11 pt-lg-20 pb-13 pb-lg-20 border-bottom border-width-1 border-default-color-2">
          <div class="row justify-content-center ">
            <div class="col-xl-7 col-lg-12" data-aos="fade-right" data-aos-duration="800" data-aos-once="true">
              <!-- cta-content start -->
              <div class="pb-xl-0 pb-9 text-xl-left text-center">
                <h2 class="text-white font-size-8 mb-4"><?=$companyName?></h2>
                  <p class="text-hit-gray font-size-5 mb-0">Built with ❤️ using <a href="https://icehrm.com">IceHrm</a></p>
              </div>
              <!-- cta-content end -->
            </div>
            <div class="col-xl-5 col-lg-12" data-aos="fade-left" data-aos-duration="800" data-aos-once="true">
              <!-- cta-btns start -->
              <div class="btns d-flex justify-content-xl-end justify-content-center align-items-xl-center flex-wrap h-100  mx-n4">
                <a class="btn btn-green btn-h-60 btn-xl mx-4 mt-6 text-uppercase" href="<?=CLIENT_BASE_URL.'apply'.$slashFix?>">View other Jobs</a>
              </div>
              <!-- cta-btns end -->
            </div>
          </div>
        </div>
      </div>
    </footer>
    <!-- footer area function end -->
  </div>
  <!-- Vendor Scripts -->
  <script src="<?=BASE_URL.'apply/'?>js/vendor.min.js"></script>
  <!-- Plugin's Scripts -->
  <script src="<?=BASE_URL.'apply/'?>plugins/fancybox/jquery.fancybox.min.js"></script>
<!--  <script src="--><?php //=BASE_URL.'apply/'?><!--plugins/nice-select/jquery.nice-select.min.js"></script>-->
  <script src="<?=BASE_URL.'apply/'?>plugins/aos/aos.min.js"></script>
  <script src="<?=BASE_URL.'apply/'?>plugins/slick/slick.min.js"></script>
  <script src="<?=BASE_URL.'apply/'?>plugins/counter-up/jquery.counterup.min.js"></script>
  <script src="<?=BASE_URL.'apply/'?>plugins/counter-up/jquery.waypoints.min.js"></script>
  <script src="<?=BASE_URL.'apply/'?>plugins/ui-range-slider/jquery-ui.js"></script>
  <!-- Activation Script -->
  <!-- <script src="js/drag-n-drop.js"></script> -->
  <script src="<?=BASE_URL.'apply/'?>js/custom.js"></script>
</body>

</html>
