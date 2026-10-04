<div class="card">
    <h5 class="card-header ">Change Password</h5>
    <div class="card-body">
        <form action="{{ route('post:user_change_password') }}" id="change_password_frm" name="change_password_frm" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-row">
                <div class="form-group col-12">
                    <label class="card-label" for="firstname">Old Password:</label>
                    <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Enter Your Old Password">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-12">
                    <label class="card-label" for="firstname">New Password:</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter Your New Password">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-12">
                    <label class="card-label" for="firstname">Confirm Password:</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Enter Your Confirm Password">
                </div>
            </div>

            <div class="form-row float-right">
                <div class="form-group float-right">
                    <button type="submit" class="btn btn-block btn-outline-secondary btnColor float-right ">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('assets/js/validation/jquery.validate.js')}}" type="text/javascript"></script>
<script>
    $(document).ready(function (){

        $("#change_password_frm").validate({
            rules: {
                old_password: {
                    required : true,
                },
                new_password: {
                    required : true,
                    minlength : 6,
                    maxlength : 16
                },
                confirm_password: {
                    required : true,
                    equalTo : "#new_password"
                },
            },
            messages: {
                old_password: {
                    required :"Please enter old password",
                },
                new_password: {
                    required :"Please enter new password",
                    minlength : "Please enter at least 6 character in password",
                    maxlength :"Please enter at most 16 character in password",
                },
                confirm_password: {
                    required :"please enter confirm password",
                    equalTo : "password and confirm password is not match"
                },

            },
            errorPlacement: function(error, element) {
                    error.insertAfter(element);
            },
            submitHandler: function(form) {
                form.submit();
            }
        });
    });
</script>
