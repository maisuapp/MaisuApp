<style>
    #radioBtn .notActive{
        color: #3276b1;
        background-color: #fff;
    }
</style>

<br /><br />
<div class="panel panel-info">
    <div class="panel-heading">
        <h2 class="panel-title">Delivery Service Type</h2>
    </div>
    <div class="panel-body">
        <div class="form-group">
            <label for="delivery_service" class="col-12 control-label ">Select Delivery Service Type ?</label>
            <div class=" col-12 col-sm-7 col-md-7">
                <div class="input-group">
                    <div id="radioBtn" class="btn-group">
                        <a class="btn btn-primary btn-sm active"  data-toggle="delivery_service" data-title="1">Delivery service</a>
                        <a class="btn btn-primary btn-sm notActive" data-toggle="delivery_service" data-title="2">Pickup service</a>
                    </div>
                    <input type="hidden" name="delivery_service" id="delivery_service" value="1">
                </div>
            </div>
        </div>
    </div>
</div>

