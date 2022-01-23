
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
