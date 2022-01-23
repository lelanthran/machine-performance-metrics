
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

async function fetchWithTimeout(resource, options = {}) {
  // From: https://dmitripavlutin.com/timeout-fetch-request/
  /* Usage:
   * async function loadGames() {
   *    try {
   *      const response = await fetchWithTimeout('/games', {
   *        timeout: 6000
   *      });
   *      const games = await response.json();
   *      return games;
   *    } catch (error) {
   *      // Timeouts if the request takes
   *      // longer than 6 seconds
   *      console.log(error.name === 'AbortError');
   *    }
   * }
   */
  const { timeout = 8000 } = options;

  const controller = new AbortController();
  const id = setTimeout(() => controller.abort(), timeout);
  const response = await fetch(resource, {
    ...options,
    signal: controller.signal
  });
  clearTimeout(id);
  return response;
}
