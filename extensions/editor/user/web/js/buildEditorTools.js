import { editorEnv } from './editorEnv';

/**
 * Builds the EditorJS `tools` configuration. Extracted verbatim from the inline
 * <script> that used to live in index.php so the legacy page and the native SPA
 * mount share one definition (no drift).
 *
 * The standard tool classes (Header, ImageTool, List, Quote, Marker, CodeTool,
 * Delimiter, InlineCode, Embed, Table, Checklist) are globals created by the
 * editor.js public tool <script>s. The custom ones (EmployeeChecklist, Quiz) are
 * exposed on window by this extension's bundle (see index.js).
 *
 * @param {object} cfg { contentId, clientBaseUrl, objectType }
 */
export default function buildEditorTools(cfg) {
  const {
    contentId,
    clientBaseUrl,
    objectType,
  } = cfg || {};

  const tools = {
    // Configure the built-in default Paragraph tool. `preserveBlank: true` keeps
    // empty paragraphs (blank lines added between paragraphs) instead of dropping
    // them on save — EditorJS's core validate() discards empty blocks unless this
    // is set. No `class` is needed: EditorJS deep-merges this config onto its
    // internal paragraph tool.
    paragraph: {
      config: { preserveBlank: true },
    },

    header: {
      class: window.Header,
      inlineToolbar: ['marker', 'link'],
      config: { placeholder: 'Header' },
      shortcut: 'CMD+SHIFT+H',
    },

    imageinline: {
      class: window.SimpleImage,
      inlineToolbar: true,
    },

    image: {
      class: window.ImageTool,
      config: {
        endpoints: {
          byFile: `${clientBaseUrl}fileupload-new.php?editor=1&&object_id=${contentId}`,
        },
      },
      features: {
        border: false,
        caption: 'optional',
        stretch: false,
      },
    },

    list: {
      class: window.List,
      inlineToolbar: true,
      shortcut: 'CMD+SHIFT+L',
    },

    checklist: {
      class: window.EmployeeChecklist,
      inlineToolbar: true,
    },

    plainchecklist: {
      class: window.Checklist,
      inlineToolbar: true,
    },

    quote: {
      class: window.Quote,
      inlineToolbar: true,
      config: {
        quotePlaceholder: 'Enter a quote',
        captionPlaceholder: "Quote's author",
      },
      shortcut: 'CMD+SHIFT+O',
    },

    marker: {
      class: window.Marker,
      shortcut: 'CMD+SHIFT+M',
    },

    quiz: {
      class: window.Quiz,
      config: {
        onSubmit: (data) => editorEnv.controller().updateQuizAnswers(window.hash, data)
          .then((response) => {
            if (response.status === 200) {
              return response.data;
            }
            return { correct: false };
          }),
      },
    },

    code: {
      class: window.CodeTool,
      shortcut: 'CMD+SHIFT+C',
    },

    delimiter: window.Delimiter,

    inlineCode: {
      class: window.InlineCode,
      shortcut: 'CMD+SHIFT+C',
    },

    embed: {
      class: window.Embed,
      inlineToolbar: true,
      shortcut: 'CMD+SHIFT+C',
    },

    table: {
      class: window.Table,
      inlineToolbar: true,
      shortcut: 'CMD+ALT+T',
    },
  };

  // Quiz tool is only for lessons (matches the legacy index.php behaviour).
  if (objectType !== 'LmsLesson' && objectType !== 'LmsEmployeeLesson') {
    delete tools.quiz;
  }

  return tools;
}
