<?php
/**
 * Public single-job page + application form.
 *
 * Data prepared in apply/index.php: $job (description/requirements already parsed
 * to HTML), $enrichedJob, $companyName, $logoFileUrl, $currency, $benifits,
 * $hiringManager, and the *_Name field aliases.
 *
 * CUSTOM FIELDS: a job definition can declare extra application-form fields via
 * its `additional_fields` JSON. extract_additional_fields() + create_field()
 * (includes/functions.php) render them below the standard fields; each posts as
 * `Custom_<Field Name>` so the backend picks them up. Keep that contract intact.
 */
require __DIR__.'/includes/functions.php';

if (!empty($job->companyName)) {
    $companyName = $job->companyName;
}
$additional_fields = extract_additional_fields($job->additional_fields);
$closingDate = $job->closingDate ? date('M j, Y', strtotime($job->closingDate)) : '';
$imageUrl = $job->attachment;
$formAction = CLIENT_BASE_URL.'api/index.php?method=post&url=/jobs/apply';
$slashFix = IS_CLOUD ? '' : '/';

$e = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$salary = null;
if ($job->showSalary === 'Yes') {
    $salary = trim($job->salaryMin.' - '.$job->salaryMax.' '.(!empty($currency) ? $currency->code : ''));
}

include __DIR__.'/includes/head.php';
?>
<main class="wrap job-detail">
    <a class="backlink" href="<?=CLIENT_BASE_URL.'apply'.$slashFix?>">&#8592; Back to all openings</a>

    <!-- Header -->
    <div class="panel">
        <div class="pad">
            <div class="job-head">
                <?php if (!empty($logoFileUrl)) { ?><img src="<?=$e($logoFileUrl)?>" alt="<?=$e($companyName)?>"><?php } ?>
                <div>
                    <h1><?=$e($job->title)?></h1>
                    <div class="company"><?=$e($companyName)?></div>
                </div>
                <?php if (!empty($closingDate)) { ?>
                    <div class="closes">Applications close<strong><?=$e($closingDate)?></strong></div>
                <?php } ?>
            </div>
        </div>

        <!-- Key facts -->
        <div class="pad divider">
            <div class="meta-grid">
                <?php if (!empty($salary)) { ?>
                <div class="meta-item">
                    <div class="k"><span>&#127974;</span> Salary</div>
                    <div class="v"><?=$e($salary)?></div>
                </div>
                <?php } ?>
                <?php if (!empty($enrichedJob->$employementTypeName)) { ?>
                <div class="meta-item">
                    <div class="k"><span>&#9203;</span> Employment</div>
                    <div class="v"><?=$e($enrichedJob->$employementTypeName)?></div>
                </div>
                <?php } ?>
                <div class="meta-item">
                    <div class="k"><span>&#128205;</span> Location</div>
                    <div class="v"><?=$e($job->location ? $job->location : $enrichedJob->country_Name)?></div>
                </div>
                <?php if (!empty($enrichedJob->$experienceLevelName)) { ?>
                <div class="meta-item">
                    <div class="k"><span>&#128200;</span> Career level</div>
                    <div class="v"><?=$e($enrichedJob->$experienceLevelName)?></div>
                </div>
                <?php } ?>
                <?php if (!empty($enrichedJob->$jobFunctionName)) { ?>
                <div class="meta-item">
                    <div class="k"><span>&#128188;</span> Category</div>
                    <div class="v"><?=$e($enrichedJob->$jobFunctionName)?></div>
                </div>
                <?php } ?>
                <?php if (!empty($enrichedJob->$educationLevelName)) { ?>
                <div class="meta-item">
                    <div class="k"><span>&#127891;</span> Education</div>
                    <div class="v"><?=$e($enrichedJob->$educationLevelName)?></div>
                </div>
                <?php } ?>
            </div>
            <?php if (!empty($benifits)) { ?>
                <div style="margin-top:22px;">
                    <div class="k" style="color:var(--c-text-faint);font-size:.82rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Benefits</div>
                    <div class="tags">
                        <?php foreach ($benifits as $benefit) { ?>
                            <span class="tag"><?=$e($benefit)?></span>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            <?php if (!empty($hiringManager)) { ?>
                <div class="chips" style="margin-top:20px;">
                    <span class="chip">
                        <?php if (!empty($hiringManager->image)) { ?>
                            <img src="<?=$e($hiringManager->image)?>" alt="" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                        <?php } ?>
                        Hiring manager: <strong style="color:var(--c-text);margin-left:4px;"><?=$e($hiringManager->first_name.' '.$hiringManager->last_name)?></strong>
                    </span>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Description -->
    <div class="panel">
        <div class="pad">
            <?php if (!empty($imageUrl)) { ?>
                <img src="<?=$e($imageUrl)?>" alt="" style="border-radius:var(--radius-sm);margin-bottom:22px;">
            <?php } ?>
            <div class="section">
                <h2 class="section-title">Job description</h2>
                <div class="prose"><?=$job->description /* allow-list sanitised in index.php */?></div>
            </div>
            <?php if (!empty(trim(strip_tags($job->requirements)))) { ?>
            <div class="section">
                <h2 class="section-title">Your role</h2>
                <div class="prose"><?=$job->requirements?></div>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- Apply -->
    <div class="panel" id="apply">
        <div class="pad">
            <div class="apply-head">
                <h2>Apply for this position</h2>
                <p>Fill in your details below &mdash; it only takes a minute.</p>
            </div>

            <div id="success-msg" class="alert alert-success" role="alert" style="display:none;">
                Thank you! We have received your application.
            </div>
            <div id="error-msg" class="alert alert-danger" role="alert" style="display:none;"></div>

            <form id="apply-form" method="POST" enctype="multipart/form-data">
                <input id="job_id" name="job_id" type="hidden" value="<?=$e($job->id)?>"/>
                <div class="form-grid">
                    <div class="field">
                        <label for="first_name">First name <span class="req">*</span></label>
                        <input id="first_name" name="first_name" type="text" class="form-control" placeholder="John" required>
                    </div>
                    <div class="field">
                        <label for="last_name">Last name <span class="req">*</span></label>
                        <input id="last_name" name="last_name" type="text" class="form-control" placeholder="Doe" required>
                    </div>
                    <div class="field">
                        <label for="email">E-mail <span class="req">*</span></label>
                        <input id="email" name="email" type="email" class="form-control" placeholder="you@example.com" required>
                    </div>
                    <div class="field">
                        <label for="phone">Telephone</label>
                        <input id="phone" name="phone" type="tel" class="form-control" placeholder="+1 555 123 4567">
                    </div>
                    <div class="field col-2">
                        <label for="cv">CV / Resume</label>
                        <input id="cv" name="cv" type="file" class="form-control" accept=".pdf,.doc,.docx">
                        <span class="hint">PDF or Word document.</span>
                    </div>
                    <div class="field col-2">
                        <label for="cover_letter">Cover letter</label>
                        <textarea id="cover_letter" name="cover_letter" class="form-control" placeholder="Tell us why you're a great fit"></textarea>
                    </div>
                    <?php
                    // Custom fields declared on the job definition (additional_fields).
                    foreach ($additional_fields as $field) {
                        echo create_field($field);
                    }
                    ?>
                    <div class="field col-2">
                        <button id="btnSubmit" type="submit" class="btn btn-primary btn-lg btn-block">Submit application</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
(function () {
    var form = document.getElementById('apply-form');
    var btn = document.getElementById('btnSubmit');
    var okMsg = document.getElementById('success-msg');
    var errMsg = document.getElementById('error-msg');

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        errMsg.style.display = 'none';
        btn.disabled = true;
        btn.textContent = 'Submitting…';

        fetch(<?=json_encode($formAction)?>, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin'
        }).then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (body) {
                return { ok: res.ok, body: body };
            });
        }).then(function (r) {
            var apiError = r.body && r.body.error;
            if (!r.ok || apiError) {
                var msg = 'An error occurred while sending your application.';
                try { msg = apiError[0][0].message; } catch (e) {}
                throw new Error(msg);
            }
            form.style.display = 'none';
            okMsg.style.display = 'block';
            okMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }).catch(function (err) {
            errMsg.textContent = err.message || 'An error occurred while sending your application.';
            errMsg.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Submit application';
        });
    });
})();
</script>
<?php include __DIR__.'/includes/foot.php'; ?>
