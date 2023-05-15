import './focal-point.scss'

function initFocalPoint() {
    let element = document.querySelector(".focal-point__button");
    if (element) {
        element.addEventListener("click", function (e) {
            e.preventDefault();
            document.querySelector("focal-point-modal").open();
        })
    }
}

window.addEventListener("koli_focal_point_init", initFocalPoint)


class FocalPointModal extends HTMLElement {
    constructor() {
      super();
    }

    connectedCallback() {
        this.preview = this.querySelector(".focal-point-preview");
        this.cursor = this.querySelector(".focal-point-preview__cursor");
        this.focalPointValue = document.querySelector(".focal-point__value");
        this.focalPointInputLeft = document.querySelector(".koli_image_focus_left");
        this.focalPointInputTop = document.querySelector(".koli_image_focus_top");
        this.saveButton = this.querySelector(".focal-point-modal__btn-save");

        let left = parseFloat(this.focalPointInputLeft.getAttribute("value"));
        let top = parseFloat(this.focalPointInputTop.getAttribute("value"));

        this.cursor.style.left = `${left * 100}%`;
        this.cursor.style.top = `${top * 100}%`;

        this.cursor.addEventListener("mousedown", this.cursorDragStart);
        document.body.addEventListener("mousemove", this.cursorDrag);
        this.saveButton.addEventListener("click", this.handleSave)
    }

    open() {
        this.classList.add("open");
        if (this.parentElement.nodeName !== 'BODY') {
            this.remove();
            document.body.append(this);
        }
    }

    disconnectedCallback() {
        document.body.removeEventListener("mousemove", this.cursorDrag);
    }

    handleClose = () => {
        this.classList.remove("open");
        let originalParent = document.querySelector(".focal-point");
        this.remove();
        originalParent.append(this);
    }

    handleSave = () => {
        this.handleClose();
        var event = new Event('change', { 'bubbles': true });
        this.focalPointInputTop.dispatchEvent(event);
    }

    cursorDragStart = (e) => {
        this.cursor.classList.add("dragging");
        this.dragging = true;
        let bb = this.cursor.getBoundingClientRect();
        this.offsetX = e.pageX - (bb.x + bb.width / 2);
        this.offsetY = e.pageY - (bb.y + bb.height / 2);
        this.updateCursorPosition(e);
        document.body.addEventListener("mouseup", this.cursorDragEnd, { once: true });
    }

    cursorDragEnd = () => {
        this.cursor.classList.remove("dragging");
        this.dragging = false;
    }

    cursorDrag = (e) => {
        if (this.dragging) {
            this.updateCursorPosition(e);
        }
    }

    updateCursorPosition = (e) => {
        let bb = this.preview.getBoundingClientRect();
        let mx = e.pageX - bb.x;
        let my = e.pageY - bb.y;
        let x = Math.min(bb.width, Math.max(0, mx  - this.offsetX)) / bb.width;
        let y = Math.min(bb.height, Math.max(0, my - this.offsetY)) / bb.height;
        this.cursor.style.top = `${y * 100}%`;
        this.cursor.style.left = `${x * 100}%`;

        this.focalPointValue.innerHTML = `${Math.round(x * 1000) / 10}% x ${Math.round(y * 1000) / 10}%`
        this.focalPointInputLeft.setAttribute("value", `${x}`)
        this.focalPointInputTop.setAttribute("value", `${y}`)
    }
}

customElements.define('focal-point-modal', FocalPointModal);