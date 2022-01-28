
function openTab(evt, tabName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
  console.log (evt.currentTarget);
  if (evt.currentTarget.renderFunc != undefined) {
    evt.currentTarget.renderFunc ();
  }
}

function startBusyMessage (message) {
   document.documentElement.style.cursor = "wait";
   document.getElementById("busyMessage").style.height = "100%";
   document.getElementById("busyMessageContent").innerHTML= message;
}

function updateBusyMessage (message) {
   document.getElementById("busyMessageContent").innerHTML= message;
}

function endBusyMessage () {
   document.getElementById("busyMessage").style.height = "0";
   document.documentElement.style.cursor = "auto";
}

async function callAPI (errorFunc, msg, url, method, obj) {
  startBusyMessage (msg);
  var body = '';
  if (obj != null) {
    body = JSON.stringify (obj);
  }
  var response = await fetch (url,
                        {
                          method: method,
                          mode: 'cors',
                          cache: 'no-cache',
                          credentials: 'same-origin',
                          headers: {
                            'Content-Type': 'application/json'
                          },
                          redirect: 'follow',
                          referrerPolicy: 'no-referrer',
                          body: body
                        });

  if (response.status < 200 || response.status > 299) {
    errorFunc ("Server returned incorrect response code: " + response.status);
  }
  var json = { "error_code": 100, "error_message": "JSON Parse error" };
  try {
    json = await response.json();
  } catch (e) {
    errorFunc ("Failed to parse JSON");
    endBusyMessage ();
    return json;
  }
  if (json.error_code !== 0) {
    errorFunc ("Server returned error [" + json.error_code + ": " + json.error_message + "]\n");
  }
  endBusyMessage ();
  return json;
}

async function callAPI_Silent (msg, url, method, obj) {
  return callAPI (() => { }, msg, url, method, obj);
}

async function callAPI_Verbose (msg, url, method, obj) {
  return callAPI ((eMesg) => { alert (eMesg); }, msg, url, method, obj);
}

