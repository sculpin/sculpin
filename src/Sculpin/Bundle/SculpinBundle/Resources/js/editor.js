var SculpinEditor = {
    init: function () {
        this.renderBar();
    },

    renderBar: function () {
        // select body element
        var body = document.getElementsByTagName('body')[0];

        // render bottom bar with edit button
        body.innerHTML += '<div style="" id="SCULPIN_BOTTOM_BAR"><BUTTON id="SCULPIN_EDIT_BUTTON">Edit This Page</BUTTON></div>';

        // create button listener
        document.getElementById("SCULPIN_EDIT_BUTTON").addEventListener('click', function () {
            SculpinEditor.renderEditor();
        });
    },

    renderEditor: function () {
        // replace bar with editor
        document.getElementById('SCULPIN_BOTTOM_BAR').style.display = 'none';

        // load editor box with content
        var body = document.getElementsByTagName('body')[0];
        body.innerHTML += '<textarea style="width: 100%; height: 100%; font-size: 1.02em; font-family: \'Roboto Mono\', monospace;" id="SCULPIN_EDIT_TEXTAREA">' + SCULPIN_EDITOR_METADATA.content + '</textarea><button id="SCULPIN_SAVE_CHANGES">Save Changes</button>';

        // create save button listener
        document.getElementById("SCULPIN_SAVE_CHANGES").addEventListener('click', function () {
            SculpinEditor.saveChanges();
        });
    },

    saveChanges: function () {
        var content = document.getElementById('SCULPIN_EDIT_TEXTAREA').value;

        // PUT content to the appropriate spot
        // this logic is temporary. Would be nice to use local storage to make sure that nothing gets lost if
        // user navs away. Also, XHR synchronous usage is deprecated, would be nice to either redo this as a
        // form submit or await the result of the async version.
        var xmlHttp = new XMLHttpRequest();

        xmlHttp.open('PUT', window.location.href, false);
        xmlHttp.setRequestHeader('Content-Type', 'application/json');
        xmlHttp.send(JSON.stringify({'diskPath': SCULPIN_EDITOR_METADATA.diskPath,'content': content}));

        document.location.reload();
    }
};

SculpinEditor.init();