@foreach($cards as $cards_value)

@php

$image = "data:image/jpeg;base64,";

try{
    $image = "data:image/jpeg;base64,". base64_encode(file_get_contents('ftp://oracleftp:9874150@erp.diu.ac/STD'.$cards_value->id.'.JPG'));
}
catch (Exception $ex){

}

@endphp
<div style="width: 100%; margin:0.3in; overflow: hidden; clear: both;">
    <div style="float: left; width: 3.2in; margin-left:0.8in; margin-right:0.3in;">
        <div style="width: 100%; height: 1.75in; clear: both; overflow: hidden; margin-top: 0.01in;">
            <img src="{{ $image }}" alt="" style="width: 1.40in;  height: 1.75in; float:right; margin-right:0.15in">
        </div>
        <div style="width: 100%; clear: both; overflow: hidden; font-size:16px; margin-top:1.7in">
            <div style="float: left; width: 2.2in; padding-left:2px;">{{ $cards_value->reg_code }}</div>
            <div style="float: left; width: 0.9in; padding-left:1px;">{{ $cards_value->session_name }}</div>
        </div>
        <div style="width: 100%; clear: both; overflow: hidden; margin-top:0.5in; padding-left:2px;">{{ $cards_value->department->department }}</div>
        <div style="width: 100%; clear: both; overflow: hidden;  font-size:16px; text-align:justify; margin-top:0.5in;  margin-left:-0.7in; line-height:24px;">
            <p>This  is to certify that <span style="text-transform: uppercase; font-weight: bold; font-family: jamesfajardo;">{{ $cards_value->name }}</span>, Son/Daughter of  <span style="text-transform: uppercase;">{{ $cards_value->f_name }} &amp;  {{ $cards_value->m_name }} </span> is a student of {{ $cards_value->department->department }} Department in Dhaka  International University.</p>
        </div>
    </div>
    <div style="float: right; width: 5.1in; margin-left:1.6in; margin-right:0in;">
        <div style="width: 100%; height: 1.75in; clear: both; overflow: hidden; margin-top: 0.01in;">
            <img src="{{ $image }}" alt="" style="width: 1.40in;  height: 1.75in; float:right;  margin-right:0.17in">
        </div>
        <div style="width: 100%; clear: both; overflow: hidden; font-size:16px; margin-top:1.49in">
            <div style="float: left; width: 3.3in; padding-left:1px; font-weight:bold;">{{ $cards_value->reg_code }}</div>
            <div style="float: left; width: 1.6in; padding-left:1px; font-weight:bold;">{{ $cards_value->session_name }}</div>
        </div>
        <div style="width: 100%; clear: both; overflow: hidden;  margin-top:0.57in; padding-left:1px; font-size:16px;  font-weight:bold;">{{ $cards_value->department->department }}</div>
        <div  style="width: 100%; clear: both; overflow: hidden; font-size:18px;  text-align:justify; margin-top:0.3in; margin-left:-0.7in;  line-height:28px;">
            <p>This is to certify that  <span style="text-transform: uppercase; font-weight: bold;">{{ $cards_value->name }}</span>,  Son/Daughter of <span style="text-transform:  uppercase;">{{ $cards_value->f_name }} &amp;  {{ $cards_value->m_name }} </span> is a  student of {{ $cards_value->department->department }} Department in Dhaka International  University.</p>
        </div>
    </div>
</div>
@endforeach