document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('deleteModal');
    if (!modal) {
        return;
    }
    modal.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;
        document.getElementById('deleteForm').setAttribute('action', trigger.getAttribute('data-url'));
        document.getElementById('deleteName').textContent = trigger.getAttribute('data-name');
    });
});
