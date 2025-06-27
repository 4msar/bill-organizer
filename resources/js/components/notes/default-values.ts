import { Options } from 'easymde';

export const defaultOptions: Options = {
    minHeight: '200px',
    toolbar: false,
    showIcons: ['heading', 'bold', 'italic', 'quote', 'link', 'code', 'unordered-list', 'ordered-list', 'horizontal-rule'],
    shortcuts: {
        drawTable: null,
        toggleBlockquote: "Cmd-'",
        toggleBold: 'Cmd-B',
        cleanBlock: null,
        toggleHeadingSmaller: 'Cmd-H',
        toggleItalic: 'Cmd-I',
        drawLink: 'Cmd-K',
        toggleUnorderedList: 'Cmd-L',
        togglePreview: null,
        toggleCodeBlock: 'Cmd-`',
        drawImage: 'Cmd-Alt-I',
        toggleOrderedList: 'Cmd-Alt-L',
        toggleHeadingBigger: 'Cmd-Shift-H',
        toggleSideBySide: null,
        toggleFullScreen: null,
    },
    spellChecker: false,
    status: false,
    styleSelectedText: false,
    placeholder: 'Write something...',
};
