<?php
$closingDate = $job->closingDate ? date('Y-m-d', strtotime($job->closingDate)) : '';
$slashFix = IS_CLOUD ? '' : '/';
?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?=$companyName?></title>
    <link rel="shortcut icon" href="<?=BASE_URL.'apply/'?>image/favicon.ico" type="image/x-icon">
    <!-- Bootstrap , fonts & icons  -->
    <link rel="stylesheet" href="<?=BASE_URL.'apply/'?>css/bootstrap.css">
    <!-- Vendor stylesheets  -->
    <link rel="stylesheet" href="<?=BASE_URL.'apply/'?>css/main.css">
    <!-- Custom stylesheet -->
<!--    <link rel="stylesheet" href="--><?php //=BASE_URL.'apply/'?><!--plugins/nice-select/nice-select.min.css">-->
</head>

<body>
<div class="site-wrapper overflow-hidden ">


    <section class="bg-white pt-12 pt-lg-24 pb-7 pb-lg-25">
        <div class="container">
            <!-- Section Title -->
            <div class="row justify-content-center mb-lg-16 mb-11">
                <div class="col-xxl-5 col-xl-6 col-lg-7 col-md-10 text-center">
                    <img src="<?=$logoFileUrl?>" alt="" style="max-height: 70px;">
                    <h2 class="mb-6 mb-lg-7 text-black-3 font-size-7"><?=$companyName?></h2>
                </div>
            </div>
            <!-- Section Title End -->
            <div class="row justify-content-center">
                <?php foreach($jobsArr as $job) { ?>
                    <div class="col-xxl-9 col-xl-9 col-lg-10 mb-8 aos-init aos-animate" data-aos="fade-right" data-aos-duration="800" data-aos-once="true">
                        <!-- Single Featured Job -->
                        <div class="pt-9 px-xl-9 px-lg-7 px-7 pb-7 light-mode-texts bg-white border-concrete rounded hover-shadow-3 " style="border: 1px solid">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="media align-items-center">
                                        <div>
                                            <h3 class="mb-0"><a class="font-size-6 heading-default-color" href="<?=CLIENT_BASE_URL.'apply'.$slashFix.'?ref='.$job->code?>"><?=$job->title?></a></h3>
                                            <a href="<?=CLIENT_BASE_URL.'apply'.$slashFix.'?ref='.$job->code?>" class="font-size-3 text-default-color line-height-2"><?=$job->companyName?></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 text-right pt-7 pt-md-5">
                                    <div class="media justify-content-md-end">
                                        <div class="image mr-5 mt-2">
                                            💼
                                        </div>
                                        <p class="font-weight-bold font-size-7 text-hit-gray mb-0"><span class="text-black-2"><?=$job->$jobFunctionName?></span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row pt-8">
                                <div class="col-md-7">
                                    <?=$job->shortDescription?>
                                </div>
                                <div class="col-md-5">
                                    <ul class="d-flex list-unstyled mr-n3 flex-wrap mr-n8 justify-content-md-end">
                                        <li class="mt-2 mr-8 font-size-small text-black-2 d-flex">
                                            <span class="mr-4" style="margin-top: -2px">📍</span>
                                            <span class="font-weight-semibold"><?=$job->location? $job->location : $job->country_Name?></span>
                                        </li>
                                        <li class="mt-2 mr-8 font-size-small text-black-2 d-flex">
                                            <span class="mr-4" style="margin-top: -2px">⏳</span>
                                            <span class="font-weight-semibold"><?=$job->$employementTypeName?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- End Single Featured Job -->
                    </div>
                <?php } ?>


        </div>
        </div>
    </section>

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
                            <a class="btn btn-green btn-h-60 btn-xl mx-4 mt-6 text-uppercase" href="<?=CLIENT_BASE_URL.'apply/'?>">View other Jobs</a>
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
<!--<script src="--><?php //=BASE_URL.'apply/'?><!--plugins/nice-select/jquery.nice-select.min.js"></script>-->
<script src="<?=BASE_URL.'apply/'?>plugins/aos/aos.min.js"></script>
<script src="<?=BASE_URL.'apply/'?>plugins/slick/slick.min.js"></script>
<script src="<?=BASE_URL.'apply/'?>plugins/counter-up/jquery.counterup.min.js"></script>
<script src="<?=BASE_URL.'apply/'?>plugins/counter-up/jquery.waypoints.min.js"></script>
<script src="<?=BASE_URL.'apply/'?>plugins/ui-range-slider/jquery-ui.js"></script>
<!-- Activation Script -->
<!-- <script src="js/drag-n-drop.js"></script> -->
<script src="js/custom.js"></script>
</body>

</html>
