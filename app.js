import ClassicEditor from '@ckeditor/ckeditor5-editor-classic/src/classiceditor';
import Essentials from '@ckeditor/ckeditor5-essentials/src/essentials';
import Paragraph from '@ckeditor/ckeditor5-paragraph/src/paragraph';
import Bold from '@ckeditor/ckeditor5-basic-styles/src/bold';
import Italic from '@ckeditor/ckeditor5-basic-styles/src/italic';
//import CKFinder from '@ckeditor/ckeditor5-ckfinder/src/ckfinder';
import {Table} from '@ckeditor/ckeditor5-table';

import CKEditorInspector from '@ckeditor/ckeditor5-inspector';
//import SourceEditing from '@ckeditor/ckeditor5-source-editing/src/sourceediting';
// Plugins to include in the build.
ClassicEditor
        .create( document.querySelector( '#editor' ), {
                plugins: [Essentials,
                    Bold,
                    Italic,
                    Image,
                    Paragraph],
                toolbar: {
                    items: [
        
                        'ckfinder',
                        'heading',
                        '|',
                        'alignment',
                        'outdent',
                        'indent',
                        '|',
                        'bold',
                        'italic',
                        'underline',
                        'strikethrough',
                        'subscript',
                        'superscript',
                        'code',
                        '-',
                        'codeBlock',
                        'blockQuote',
                        'link',
                        'uploadImage',
                        'insertTable',
                        'mediaEmbed',
                        '|',
                        'bulletedList',
                        'numberedList',
                        'todoList',
                        '|',
                        'undo',
                        'redo',
                        '|',
                        'sourceEditing'
                    ],
                    shouldNotGroupWhenFull: true
                },
                table: {
                    contentToolbar: [
                        'tableColumn', 'tableRow', 'mergeTableCells',
                        'tableProperties', 'tableCellProperties'
                    ]
                },
                            ckfinder: {
                                // Upload the images to the server using the CKFinder QuickUpload command.
                                uploadUrl: '/assets/vendors/modules/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images&responseType=json'
                            },
                image: {
                    toolbar: [
                        'linkImage',
                        '|',
                        'imageStyle:block',
                        'imageStyle:side',
                        '|',
                        'imageTextAlternative',
                        'toggleImageCaption'
                    ]
                },
                htmlSupport: {
                    allow: [
                        {
                            name: /.*/,
                            attributes: true,
                            classes: true,
                            styles: true
                        }
                    ],
                    disallow: [
                        {
                            attributes: [
                                { key: /^on(.*)/i, value: true },
                                { key: /.*/, value: /(\b)(on\S+)(\s*)=|javascript:|(<\s*)(\/*)script/i },
                                { key: /.*/, value: /data:(?!image\/(png|jpg|jpeg|gif|webp))/i }
                            ]
                        },
                        { name: 'script' }
                    ]
                }
            } )
         .then( editor => {
            CKEditorInspector.attach( editor );
        } )
        .catch( error => {
            console.error( error );
        } );