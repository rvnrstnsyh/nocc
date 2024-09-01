/**
 * Update "Port" textbox at login page.
 *
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */
function updateLoginPort() {
  const form = document.getElementById("nvll_webmail_login");
  const servType = form.servtype.options[form.servtype.selectedIndex].value;
  const portMap = {
    "imap": 143,
    "notls": 143,
    "ssl": 993,
    "ssl/novalidate-cert": 993,
    "pop3": 110,
    "pop3/notls": 110,
    "pop3/ssl": 995,
    "pop3/ssl/novalidate-cert": 995,
  };

  form.port.value = portMap[servType] || "";
}

/**
 * Update login page.
 */
function updateLoginPage(id = "") {
  const form = document.getElementById("nvll_webmail_login");

  if (form.user.value === "" && form.passwd.value === "") {
    const theme = form.theme?.[form.theme.selectedIndex]?.value;
    const lang = form.lang?.[form.lang.selectedIndex]?.value;
    const lang_page = `index.php?${id}${theme ? `&theme=${theme}` : ""}${
      lang ? `&lang=${lang}` : ""
    }`;

    self.location = lang_page;
  }
}

/**
 * This array is used to remember mark status of rows.
 */
const marked_row = {};

/**
 * Enables highlight and marking of rows in inbox table.
 *
 * Based on the PMA_markRowsInit() function from phpMyAdmin <http://www.phpmyadmin.net/>.
 */
function markInboxRowsInit() {
  const inboxTable = document.getElementById("inboxTable");

  if (!inboxTable) return;

  const rows = inboxTable.getElementsByTagName("tr");

  Array.from(rows).forEach((row) => {
    const className = row.className;

    if (!className.startsWith("odd") && !className.startsWith("even")) return;
    if (navigator.appName === "Microsoft Internet Explorer") {
      row.onmouseover = () => row.classList.add("hover");
      row.onmouseout = () => row.classList.remove("hover");
    }

    row.onmousedown = () => {
      const checkbox = row.querySelector("input[type='checkbox']");
      const unique_id = checkbox ? checkbox.name + checkbox.value : row.id;

      if (unique_id) {
        marked_row[unique_id] = !marked_row[unique_id];
        row.classList.toggle("marked", marked_row[unique_id]);

        if (checkbox && !checkbox.disabled) {
          checkbox.checked = marked_row[unique_id];
        }
      }
    };

    const checkbox = row.querySelector("input[type='checkbox']");

    if (checkbox) {
      checkbox.onclick = () => {
        checkbox.checked = !checkbox.checked;
        return false;
      };
    }
  });
}

window.onload = markInboxRowsInit;

/**
 * Invert checked messages in inbox table.
 * Based on the markAllRows() and unMarkAllRows() functions from phpMyAdmin <http://www.phpmyadmin.net/>.
 */
function InvertCheckedMsgs() {
  const inboxTable = document.getElementById("inboxTable");

  if (!inboxTable) return true;

  const rows = inboxTable.getElementsByTagName("tr");

  Array.from(rows).forEach((row) => {
    const checkbox = row.querySelector("input[type='checkbox']");
    const unique_id = checkbox ? checkbox.name + checkbox.value : "";

    if (checkbox && unique_id) {
      checkbox.checked = !checkbox.checked;
      marked_row[unique_id] = checkbox.checked;
      row.classList.toggle("marked", checkbox.checked);
    }
  });

  return true;
}

/**
 * Handle marker for changes in inbox
 */
let nvll_cur_num_msg = 0;
let nvll_session = "";
let nvll_timer = 600;
let nvll_message = "Your inbox content has changed.";
let nvll_alert = true;

function ShowInboxChangedMarker() {
  const els = document.getElementsByClassName("inbox_changed");
  Array.from(els).forEach((el) => el.style.display = "inline");

  if (nvll_alert) {
    alert(nvll_message);
    nvll_alert = false;
    window.location.reload();
  }
  return true;
}

const xhttp = new XMLHttpRequest();

xhttp.onreadystatechange = function () {
  if (this.readyState === 4 && this.status === 200) {
    const cur_num_msg = parseInt(this.responseText, 10);
    if (cur_num_msg !== -1 && cur_num_msg !== nvll_cur_num_msg) {
      ShowInboxChangedMarker();
    }
  }
  return true;
};

function GetInboxChangedHandler() {
  xhttp.open(
    "GET",
    `api.php?${nvll_session}&service=inbox_changed&num_msg=${nvll_cur_num_msg}`,
  );
  xhttp.send();
  return true;
}

function InitInboxChangedHandler(
  cur_num_msg,
  session,
  timer = 600,
  message = nvll_message,
  show_alert = true,
) {
  nvll_session = session;
  nvll_timer = timer;
  nvll_message = message;
  nvll_alert = show_alert;
  nvll_cur_num_msg = cur_num_msg;

  if (timer > 0) setInterval(GetInboxChangedHandler, timer * 1000);
  return true;
}
