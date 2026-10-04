<div>
    <div class="form-group row" id="every_d">
        <div class="col-sm-4">
            <div class="border-checkbox-section row">
                <div class="border-checkbox-group border-checkbox-group-primary">
                    <input name="day[all]"
                           value="1"
                           class="border-checkbox"
                           @if(!isset($store_open_timings) || isset($store_open_timings) && count($store_open_timings)==1)
                           checked
                           @endif
                           type="checkbox"
                           id="checkbox_every">
                    <label class="border-checkbox-label"
                           for="checkbox_every">
                    </label>
                    <span>EveryDay</span>
                </div>
            </div>
        </div>

        <div class="every_d_opn_time input-top-bottom-margin col-sm-4"
             @if(isset($store_open_timings) && count($store_open_timings)>1) style="display:none" @endif>
            <input type="text"
                   class="form-control timepicker required time_all"
                   id=""
                   name="open_time[0]"
                   value="{{ (isset($store_open_timings)) ? ((array_key_exists("all",$store_open_timings)) ? $store_open_timings['all'] : '' ) : old('open_time.0') }}">
            <span class="input-group-addon">
                <span class="glyphicon glyphicon-time"></span>
            </span>
        </div>
        <div class="col-sm-4 every_d_cls_time"
             @if(isset($store_open_timings) && count($store_open_timings)>1) style="display:none" @endif>
            <input type="text"
                   class="form-control timepicker required time_all"
                   name="close_time[0]"
                   value="{{ (isset($store_close_timings)) ? ((array_key_exists("all",$store_close_timings)) ? $store_close_timings['all'] : '' ) : old('close_time.0') }}">
            <span class="input-group-addon">
                <span class="glyphicon glyphicon-time"></span>
            </span>
        </div>
    </div>
</div>

<div id="day_wise"
     @if(!isset($store_open_timings) || isset($store_open_timings) && count($store_open_timings)==1) style="display: none;" @endif>
    {{--style="display: none"--}}
    <?php $days = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"]; ?>
    @for($i=0;$i<count($days);$i++)
        <div class="form-group row">
            <div class="col-sm-4">
                <div class="border-checkbox-section row">
                    <div class="border-checkbox-group border-checkbox-group-primary">
                        <input name="day[{{$days[$i]}}]"
                               value="1"
                               class="border-checkbox checkbox_all"
                               @if(isset($store_open_timings) && ($store_open_timings[$days[$i]] == "00:00:00" && $store_close_timings[$days[$i]] == "00:00:00" ))

                               @elseif(isset($store_open_timings) && array_key_exists($days[$i],$store_open_timings) || !isset($store_open_timings))
                               checked
                               @endif
                               type="checkbox"
                               id="checkbox_{{$days[$i]}}">
                        <label class="border-checkbox-label"
                               for="checkbox_{{$days[$i]}}">
                        </label>
                        {{ $days[$i] }}
                    </div>
                </div>
            </div>
            <div class="input-top-bottom-margin col-sm-4">
                @if(isset($store_open_timings) &&  array_key_exists($days[$i],$store_open_timings) )
                    @if($store_open_timings[$days[$i]] > Date('12:00:00') && $store_open_timings[$days[$i]]  < Date('13:00:00'))
                        {{--                        <span class="timepicker">--}}
                        <input type="text"
                               class="form-control timepicker new_picker check_open_type_value_{{$i+1}}"
                               check_open_type_value="{{$i+1}}"
                               check_type="1"
                               name="open_time[{{$i+1}}]"
                               value="{{ (isset($store_open_timings)) ? ((array_key_exists($days[$i],$store_open_timings)) ? Date('H:i', strtotime($store_open_timings[$days[$i]]))  .' PM': '' ) : old("open_time.$i+1") }}">
                        {{--                                <input type="hidden"--}}
                        {{--                                       class="form-control timepicker_check check_open_type_value_{{$i+1}}"--}}
                        {{--                                       check_open_type_value="{{$i+1}}"--}}
                        {{--                                       check_type="1"--}}
                        {{--                                       name="open_time[{{$i+1}}]"--}}
                        {{--                                       value="{{ (isset($store_open_timings)) ? ((array_key_exists($days[$i],$store_open_timings)) ? $store_open_timings[$days[$i]] : '' ) : old("open_time.$i+1") }}">--}}
                        {{--                            </span>--}}
                    @else
                        <input type="text"
                               class="form-control timepicker check_open_type_value_{{$i+1}}"
                               check_open_type_value="{{$i+1}}"
                               check_type="1"
                               name="open_time[{{$i+1}}]"
                               value="{{ (isset($store_open_timings)) ? ((array_key_exists($days[$i],$store_open_timings)) ? $store_open_timings[$days[$i]] : '' ) : old("open_time.$i+1") }}">
                    @endif
                @else
                    <input type="text"
                           class="form-control timepicker check_open_type_value_{{$i+1}}"
                           check_open_type_value="{{$i+1}}"
                           check_type="1"
                           name="open_time[{{$i+1}}]"
                           value="{{ (isset($store_open_timings)) ? ((array_key_exists($days[$i],$store_open_timings)) ? $store_open_timings[$days[$i]] : '' ) : old("open_time.$i+1") }}">
                @endif
                <span class="error check_open_type_error_{{$i+1}}"></span>
            </div>
            <div class="col-sm-4">
                @if(isset($store_close_timings) &&  array_key_exists($days[$i],$store_close_timings) )
                    @if($store_close_timings[$days[$i]] > Date('12:00:00') && $store_close_timings[$days[$i]]  < Date('13:00:00'))
                        <span class="timepicker">
                                <input type="text"
                                       class="form-control new_picker check_close_type_value_{{$i+1}}"
                                       check_close_type_value="{{$i+1}}"
                                       check_type="2"
                                       name="show_new_close_time[{{$i+1}}]"
                                       value="{{ (isset($store_close_timings)) ? ((array_key_exists($days[$i],$store_close_timings)) ? Date('H:i', strtotime($store_close_timings[$days[$i]]))  .' PM': '' ) : old("close_time.$i+1") }}">
                                <input type="hidden"
                                       class="form-control timepicker_check check_close_type_value_{{$i+1}}"
                                       check_close_type_value="{{$i+1}}"
                                       check_type="2"
                                       name="close_time[{{$i+1}}]"
                                       value="{{ (isset($store_close_timings)) ? ((array_key_exists($days[$i],$store_close_timings)) ? $store_close_timings[$days[$i]] : '' ) : old("close_time.$i+1") }}">
                        </span>
                    @else
                        <input type="text"
                               class="form-control timepicker check_close_type_value_{{$i+1}}"
                               check_close_type_value="{{$i+1}}"
                               check_type="2"
                               name="close_time[{{$i+1}}]"
                               value="{{ (isset($store_close_timings)) ? ((array_key_exists($days[$i],$store_close_timings)) ? (($store_close_timings[$days[$i]]=="00:00:00")?"23:59:59":$store_close_timings[$days[$i]]  ) : '' ) : old("close_time.$i+1") }}">
                    @endif
                @else
                    <input type="text"
                           class="form-control timepicker check_close_type_value_{{$i+1}}"
                           check_close_type_value="{{$i+1}}"
                           check_type="2"
                           name="close_time[{{$i+1}}]"
                           value="{{ (isset($store_close_timings)) ? ((array_key_exists($days[$i],$store_close_timings)) ?(($store_close_timings[$days[$i]]=="00:00:00")?"23:59:59":$store_close_timings[$days[$i]]  ) : '' ) : old("close_time.$i+1") }}">
                @endif
                <span class="error check_close_type_error_{{$i+1}}"></span>
            </div>
        </div>
    @endfor
</div>
