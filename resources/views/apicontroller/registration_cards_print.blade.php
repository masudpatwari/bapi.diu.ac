@foreach($cards as $cards_value)

@php

$image = "data:image/jpeg;base64,";

try{
    $image = "data:image/jpeg;base64,". base64_encode(file_get_contents('ftp://oracleftp:9874150@erp.diu.ac/STD'.$cards_value->id.'.JPG'));
}
catch (Exception $ex){

}

@endphp
<div class="clear">
    <div class="leftLayOut">
        <div class="clear imgLayout" style="padding-top: 0.35in; padding-right:0.20in">
            <div class="imgBox" style="">
                <img src="{{ $image }}" alt="">
            </div>
        </div>
        <div class="clear regCodeSession" style="margin-top:1.6in">
            <div class="leftRegCode" style="margin-left: 1.40in">{{ $cards_value->reg_code }}</div>
            <div class="leftSession" style="margin-right: 0.20in">{{ $cards_value->session_name }}</div>
        </div>
        <div class="clear leftDepartment" style="margin-top:0.35in; margin-left: 1.40in">{{ $cards_value->department->department }}</div>
        <p style="margin-top: 0.80in; margin-left: 0.60in;">This  is to certify that <span style="text-transform: uppercase; font-weight: bold; font-family: jamesfajardo;">{{ $cards_value->name }}</span>, Son/Daughter of  <span style="text-transform: uppercase;">{{ $cards_value->f_name }} &amp;  {{ $cards_value->m_name }} </span> is a student of {{ $cards_value->department->department }} Department in Dhaka  International University.</p>
    </div>
    <div class="rightLayOut">
        <div class="clear imgLayout" style="padding-top: 0.35in; padding-right:0.35in">
            <div class="imgBox" style="">
                <img src="{{ $image }}" alt="">
            </div>
        </div>
        <div class="clear" style="margin-top:1.40in">
            <div class="rightRegCode" style="margin-left: 1.50in">{{ $cards_value->reg_code }}</div>
            <div class="rightSession" style="margin-right: 0.35in">{{ $cards_value->session_name }}</div>
        </div>
        <div class="clear rightDepartment" style="margin-top:0.40in; margin-left: 1.50in">{{ $cards_value->department->department }}</div>
        <p style="margin-top: 0.30in; margin-left: 0.80in">This is to certify that  <span style="text-transform: uppercase; font-weight: bold;">{{ $cards_value->name }}</span>,  Son/Daughter of <span style="text-transform:  uppercase;">{{ $cards_value->f_name }} &amp;  {{ $cards_value->m_name }} </span> is a  student of {{ $cards_value->department->department }} Department in Dhaka International  University.</p>
    </div>
</div>
@endforeach