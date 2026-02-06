<?php
use EditorUser\EditorService;

$moduleData = [
	'controller_url' => CLIENT_BASE_URL.'service.php',
	'user_level' => $user->user_level,
];
$content = null;

if (isset($_REQUEST['object']) && isset($_REQUEST['id']) && isset($_REQUEST['field'])) {
	$object = EditorService::getRelatedObject($_REQUEST['object'], $_REQUEST['id']);
	$access = EditorService::getObjectAccess($object);
    if (!in_array('element', $access)) {
        $content = EditorService::getContent($_REQUEST['object'], $_REQUEST['id'], $_REQUEST['field']);
    }
	if ($content === null && !in_array('save', $access)) {
		echo 'You are not allowed to create this document';
		exit();
	}

    if ($content === null && in_array('save', $access)) {
        $content = EditorService::createContent($_REQUEST['object'], $_REQUEST['id'], $_REQUEST['field'], $_REQUEST['title'] ?? null);
    }
}

if (!isset($_REQUEST['hash']) && $content !== null && !empty($content->hash)) {
	$string = '<script type="text/javascript">';
	$string .= 'window.location = "' . CLIENT_BASE_URL.'?g=extension&n=editor|user&m='.$_REQUEST['m'].'&hash='.$content->hash . '"';
	$string .= '</script>';

	echo $string;
    exit();
}

if (!isset($_REQUEST['hash'])) {
	echo 'Not found';
	exit();
}

$content = EditorService::getContentByHash($_REQUEST['hash']);
/** @var \Model\BaseModel $object */
$object = EditorService::getRelatedObject($content->object_type, $content->object_id);
if (!$object || empty($object->id)) {
	echo 'No object for the document';
	exit();
}
$access = EditorService::getObjectAccess($object);

if (!in_array('element', $access)) {
	echo 'Not allowed to view the document';
	exit();
}
$data = json_decode($content->content) ?? null;
$readOnly = ($_REQUEST['view'] == '1' || !in_array('save', $access))?'true':'false';

$readOnlyTypes = [
    'LmsEmployeeCourse',
    'LmsEmployeeLesson',
];

if (in_array($content->object_type, $readOnlyTypes)) {
	$readOnly = 'true';
}
$canSelectCheckBoxes = ($_REQUEST['checks'] == '1' && in_array('element', $access))?'true':'false';

$editorPermissions = $object->getEditorPermissions();
if (in_array('default', $editorPermissions)) {
    // do nothing
} else {
    if ($readOnly == 'false' && !in_array('edit', $editorPermissions)) {
        $readOnly = 'true';
    }

    if (in_array('view', $editorPermissions)) {
		$readOnly = 'true';
    }

	if (in_array('check', $editorPermissions)) {
		$canSelectCheckBoxes = 'true';
	}
}


$employees = EditorService::getEmployeeNamesAndImages();
$sideBarObject = $object->getEditorSideBarObject($readOnly === 'true'?'view':'edit');
?>
<link href="https://fonts.googleapis.com/css?family=PT+Mono" rel="stylesheet">
<link href="<?=BASE_URL?>js/editorjs/public/assets/demo.css" rel="stylesheet">
<script src="<?=BASE_URL?>js/editorjs/public/assets/json-preview.js"></script>
<style>
    .codex-editor {
        border: none;
    }

</style>
<div class="ce-example">
    <div class="row">
        <div class="col-md-8">
            <div class="ce-example__content _ce-example__content--small">
                <div id="editorjs"></div>
            </div>
        </div>
        <div class="col-md-4">
            <div id="content"></div>
            <div id="EmployeeSelect"></div>
        </div>
    </div>

</div>

<script src="<?=BASE_URL?>js/editorjs/public/js/header.js"></script><!-- Header (https://cdn.jsdelivr.net/npm/@editorjs/header@latest)-->
<script src="<?=BASE_URL?>js/editorjs/public/js/simple-image.js"></script><!-- Image (https://cdn.jsdelivr.net/npm/@editorjs/simple-image@latest)-->
<script src="<?=BASE_URL?>js/editorjs/public/js/delimiter.js"></script><!-- Delimiter (https://cdn.jsdelivr.net/npm/@editorjs/delimiter@latest)-->
<script src="<?=BASE_URL?>js/editorjs/public/js/list.js"></script><!-- List (https://cdn.jsdelivr.net/npm/@editorjs/list@latest)-->
<script src="<?=BASE_URL?>js/editorjs/public/js/quote.js"></script><!-- Quote (https://cdn.jsdelivr.net/npm/@editorjs/quote@latest)-->
<script src="<?=BASE_URL?>js/editorjs/public/js/code.js"></script><!-- Code (https://cdn.jsdelivr.net/npm/@editorjs/code@latest)-->
<script src="<?=BASE_URL?>js/editorjs/public/js/embed.js"></script><!-- Embed (https://cdn.jsdelivr.net/npm/@editorjs/embed@latest)-->
<script src="<?=BASE_URL?>js/editorjs/public/js/table.js"></script><!-- Table (https://cdn.jsdelivr.net/npm/@editorjs/table@latest)-->
<script src="<?=BASE_URL?>js/editorjs/public/js/link.js"></script><!-- Link (https://cdn.jsdelivr.net/npm/@editorjs/link@latest)-->
<script src="<?=BASE_URL?>js/editorjs/public/js/warning.js"></script><!-- Warning (https://cdn.jsdelivr.net/npm/@editorjs/warning@latest)-->
<script src="<?=BASE_URL?>js/editorjs/public/js/checklist.js"></script><!-- Checklist (https://cdn.jsdelivr.net/npm/@editorjs/checklist@latest)-->
<script src="<?=BASE_URL?>js/editorjs/public/js/marker.js"></script><!-- Marker (https://cdn.jsdelivr.net/npm/@editorjs/marker@latest)-->
<script src="<?=BASE_URL?>js/editorjs/public/js/inline-code.js"></script><!-- Inline Code (https://cdn.jsdelivr.net/npm/@editorjs/inline-code@latest) -->
<script src="<?=BASE_URL?>js/editorjs/public/js/image.js"></script><!-- Image (https://cdn.jsdelivr.net/npm/@editorjs/image@latest) -->

<!-- Load Editor.js's Core -->
<script src="<?=BASE_URL?>js/editorjs/public/js/editorjs.js"></script>  <!-- https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest-->


<!-- Initialization -->
<script>

    initEditorUser(<?=json_encode($moduleData)?>);


    data = <?= $data?json_encode($data):'{}' ?>;
    window.content_id = '<?=$content->id?>';
    window.hash = '<?=$content->hash?>';
    window.object_type = '<?=$content->object_type?>';
    window.object_id = '<?=$content->object_id?>';
    window.object_field = '<?=$content->object_field?>';
    window.editor_readonly = <?=$readOnly?>;
    window.editor_can_select_checks = <?=$canSelectCheckBoxes?>;
    window.editorEmployees = <?= json_encode($employees)?>;
    window.sideBarObject = <?= json_encode($sideBarObject)?>;


    var selectedTools = {
        /**
         * Each Tool is a Plugin. Pass them via 'class' option with necessary settings {@link docs/tools.md}
         */
        header: {
            class: Header,
            inlineToolbar: ['marker', 'link'],
            config: {
                placeholder: 'Header'
            },
            shortcut: 'CMD+SHIFT+H'
        },

        /**
         * Or pass class directly without any configuration
         */
        imageinline: {
            class: SimpleImage,
            inlineToolbar: true,
        },

        image: {
            class: ImageTool,
            config: {
                endpoints: {
                    byFile: '<?=CLIENT_BASE_URL?>fileupload-new.php?editor=1&'+'&object_id='+window.content_id,
                }
            },
            features: {
                border: false,
                caption: 'optional',
                stretch: false
            }
        },

        list: {
            class: List,
            inlineToolbar: true,
            shortcut: 'CMD+SHIFT+L'
        },

        checklist: {
            class: EmployeeChecklist,
            inlineToolbar: true,
        },

        plainchecklist: {
            class: Checklist,
            inlineToolbar: true,
        },

        quote: {
            class: Quote,
            inlineToolbar: true,
            config: {
                quotePlaceholder: 'Enter a quote',
                captionPlaceholder: 'Quote\'s author',
            },
            shortcut: 'CMD+SHIFT+O'
        },

        marker: {
            class:  Marker,
            shortcut: 'CMD+SHIFT+M'
        },

        quiz: {
            class: Quiz,
            config: {
                onSubmit: (data) => {
                    console.log(JSON.stringify(data));
                    return window.editorExtensionController.updateQuizAnswers(window.hash, data)
                        .then((response) => {
                            console.log(response);
                            if (response.status === 200) {
                                return response.data;
                            }
                            return {correct:false};
                        });
                },
            },
        },

        code: {
            class:  CodeTool,
            shortcut: 'CMD+SHIFT+C'
        },

        delimiter: Delimiter,

        inlineCode: {
            class: InlineCode,
            shortcut: 'CMD+SHIFT+C'
        },

        // linkTool: {
        //     class: LinkTool,
        //     config: {
        //         endpoint: 'http://localhost:8008/fetchUrl', // Your backend endpoint for url data fetching, https://github.com/editor-js/link
        //     }
        // },

        embed: {
            class: Embed,
            inlineToolbar: true,
            shortcut: 'CMD+SHIFT+C'
        },

        table: {
            class: Table,
            inlineToolbar: true,
            shortcut: 'CMD+ALT+T'
        },

    };

    if (window.object_type !== 'LmsLesson' && window.object_type !== 'LmsEmployeeLesson') {
        delete selectedTools.quiz;
    }

    /**
     * To initialize the Editor, create a new instance with configuration object
     * @see docs/installation.md for mode details
     */
    window.editor = new EditorJS({
        /**
         * Enable/Disable the read only mode
         */
        readOnly: editor_readonly,

        /**
         * Wrapper of Editor
         */
        holder: 'editorjs',

        /**
         * Common Inline Toolbar settings
         * - if true (or not specified), the order from 'tool' property will be used
         * - if an array of tool names, this order will be used
         */
        // inlineToolbar: ['link', 'marker', 'bold', 'italic'],
        // inlineToolbar: true,

        /**
         * Tools list
         */
        tools: selectedTools,

        /**
         * This Tool will be used as default
         */
        // defaultBlock: 'paragraph',

        /**
         * Initial Editor data
         */
        data: data,
        onReady: function(){
            //saveButton.click();
        },
        onChange: function(api, event) {
            //saveButton.click();
        }
    });

    /**
     * Saving button
     */
    const saveButton = document.getElementById('saveButton');

    /**
     * Toggle read-only button
     */
    const toggleReadOnlyButton = document.getElementById('toggleReadOnlyButton');
    const readOnlyIndicator = document.getElementById('readonly-state');

</script>

