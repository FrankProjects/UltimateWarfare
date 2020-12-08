function updateStatus(status) {
    let btnStatus = document.getElementById('chat-status');
    btnStatus.classList.value = '';
    btnStatus.classList.add('btn');

    switch (status) {
        case 'online':
            btnStatus.classList.add('btn-success');
            btnStatus.innerText = 'Online';
            break;
        case 'connecting':
            btnStatus.classList.add('btn-warning');
            btnStatus.innerText = 'Connecting';
            break;
        case 'offline':
            btnStatus.classList.add('btn-inverse');
            btnStatus.innerText = 'Offline';
            break;
        case 'error':
            btnStatus.classList.add('btn-danger');
            btnStatus.innerText = 'Offline';
            break;
    }
}

console.log('Ready');
updateStatus('connecting');
let socket = new WebSocket('ws://' + socketHost + ':' + socketPort);

socket.onopen = function () {
    console.log('Connection successful');
    updateStatus('online');
};

socket.onclose = function (event) {
    if (event.wasClean) {
        console.log('Connection closed.');
        updateStatus('offline');
    } else {
        console.log('Connection killed.');
        updateStatus('error');
    }
    console.log(event.code + event.reason);
};

socket.onmessage = function (event) {
    console.log('new message');

    let data = JSON.parse(event.data);
    let list = document.getElementById('list');
    let message = document.createElement("div");
    let node = document.createTextNode(event.data);
    message.appendChild(node);
    message.classList.add('comment');
    let time = Date.now()
    message.innerHTML = '<h2>' + data.name + '<br /><span class="time-ago" title="' + time + '">' + time + '</span></h2><p>' + data.message + '</p>';
    //list.appendChild(message);


    let message2 = document.createElement("div");
    let node2 = document.createTextNode(event.data);
    message2.appendChild(node2);
    message2.classList.add('d-flex');
    message2.classList.add('justify-content-start');
    message2.classList.add('mb-4');
    let date = new Date();
    message2.innerHTML = '<div class="msg_cotainer">' + data.message + '<span class="msg_time">' + date.toLocaleString() + '</span></div>';
    list.appendChild(message2);
    message2.scrollIntoView();
};

socket.onerror = function (error) {
    console.log(error.message);
};

let button = document.getElementById('send');
let textarea = document.getElementById('message-box');

function sendText() {
    let text = textarea.value;
    if (text.length > 0) {
        socket.send(JSON.stringify({'type': 'message', 'message': text}));
        textarea.value = '';

        let list = document.getElementById('list');
        let message = document.createElement("div");
        let node = document.createTextNode(text);
        message.appendChild(node);
        message.classList.add('comment');
        message.classList.add('mine');
        let time = Date.now()
        message.innerHTML = '<h2>Me<br /><span class="time-ago" title="' + time + '">' + time + '</span></h2><p>' + text + '</p>';
        //list.appendChild(message);

        let message2 = document.createElement("div");
        let node2 = document.createTextNode(text);
        message2.appendChild(node2);
        message2.classList.add('d-flex');
        message2.classList.add('justify-content-end');
        message2.classList.add('mb-4');
        let date = new Date();
        message2.innerHTML = '<div class="msg_cotainer_send">' + text + '<span class="msg_time_send">' + date.toLocaleString() + '</span></div>';
        list.appendChild(message2);
        message2.scrollIntoView();
/**
        <div class="d-flex justify-content-end mb-4">
            <div class="msg_cotainer_send">
                Test
                <span class="msg_time_send">8:00 AM, Today</span>
            </div>
        </div>
 */
        return true;
    }

    return false;
}

button.onclick = sendText;

textarea.onkeypress = function (ev) {
    if (ev.key === 'Enter') {
        ev.preventDefault();
        sendText();
    }
};
