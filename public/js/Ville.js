jQuery(document).ready(function($) {
    $(".hidden-form").hide();

    $(".edit-btn").click(function() {
        var formId = $(this).data("form-id");
        var form = $("#form_" + formId);

        $(".editable-content", form).toggle();
        $(".hidden-form", form).toggle();
    });
});