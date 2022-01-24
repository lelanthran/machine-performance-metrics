
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
}

function startBusyMessage (message) {
   console.log (message);
   document.documentElement.style.cursor = "wait";
   document.getElementById("busyMessage").style.height = "100%";
   document.getElementById("busyMessageContent").innerHTML= message;
}

function updateBusyMessage (message) {
   console.log (message);
   document.getElementById("busyMessageContent").innerHTML= message;
}

function endBusyMessage () {
   console.log ("end busyMessage");
   document.getElementById("busyMessage").style.height = "0";
   document.documentElement.style.cursor = "auto";
}

async function callAPI (msg, url, method) {
  startBusyMessage (msg);
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
                          body: JSON.stringify ('')
                        });

  if (response.status < 200 || response.status > 299) {
    alert ("Server returned incorrect response code: " + response.status);
  }
  var json = { "error_code": 100, "error_message": "JSON Parse error" };
  try {
    json = await response.json();
  } catch (e) {
    alert ("Failed to parse JSON");
    endBusyMessage ();
    return json;
  }
  if (json.error_code !== 0) {
    alert ("Error retrieving data from server\n");
  }
  endBusyMessage ();
  return json;
}
