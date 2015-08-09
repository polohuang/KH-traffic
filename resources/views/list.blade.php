<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js" type="text/javascript"></script>
        <title>KH-routing</title>
        <style>
            html, body, #map-canvas {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        </style>
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true"></script>
        <script>
            function initialize() {
                var myLatlng = new google.maps.LatLng({{$result[0]->VD_lon}},{{$result[0]->VD_lat}});
                var styles=[{featureType:"administrative",elementType:"all",stylers:[{visibility:"on"},{lightness:33}]},{featureType:"landscape",elementType:"all",stylers:[{color:"#f2e5d4"}]},{featureType:"poi.park",elementType:"geometry",stylers:[{color:"#c5dac6"}]},{featureType:"poi.park",elementType:"labels",stylers:[{visibility:"on"},{lightness:20}]},{featureType:"road",elementType:"all",stylers:[{lightness:20}]},{featureType:"road.highway",elementType:"geometry",stylers:[{color:"#c5c6c6"}]},{featureType:"road.arterial",elementType:"geometry",stylers:[{color:"#e4d7c6"}]},{featureType:"road.local",elementType:"geometry",stylers:[{color:"#fbfaf7"}]},{featureType:"water",elementType:"all",stylers:[{visibility:"on"},{color:"#acbcc9"}]}];
                var mapOptions = {
                    zoom: 16,
                    center: myLatlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    styles: styles
                }
                var markers = [
                    @for ($i = 0; $i < sizeof($result); $i++)
                        @if($i != sizeof($result))
                            {!!'['.$result[$i]->VD_lon.','.$result[$i]->VD_lat.',"'.$result[$i]->avgspeed.'"],'!!}
                        @else
                            {!!'['.$result[$i]->VD_lon.','.$result[$i]->VD_lat.',"'.$result[$i]->avgspeed.'"]'!!}
                        @endif
                    @endfor
                ];
                var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
                for (i = 0; i < markers.length; i++) {  
                    marker = new google.maps.Marker({
                        position: new google.maps.LatLng(markers[i][0], markers[i][1]),
                        map: map,
                        icon: 'http://maps.google.com/mapfiles/ms/icons/'+markers[i][2]+'-dot.png'
                    });
                }
            }

            google.maps.event.addDomListener(window, 'load', initialize);


        </script>
        <script type="text/javascript">
            
            //檢查瀏覽器
            var isIE7 = navigator.userAgent.search("MSIE 7") > -1;
            var isIE8 = navigator.userAgent.search("MSIE 8") > -1;
            var isFirefox = navigator.userAgent.search("Firefox") > -1;
            var isOpera = navigator.userAgent.search("Opera") > -1;
            var isSafari = navigator.userAgent.search("Safari") > -1;//Google瀏覽器是用這核心
            if (isIE7) {

                alert('您現在使用的瀏覽器為IE7，請使用IE9版本以上或是Google Chrome瀏覽器\n否則可能會造成畫面呈現的問題，謝謝您的配合');
            }
            if (isIE8) {

                alert('您現在使用的瀏覽器為IE8，請使用IE9版本以上或是Google Chrome瀏覽器\n否則可能會造成畫面呈現的問題，謝謝您的配合');
            }
            if (isOpera) {

                alert('您現在使用的瀏覽器為Opera，請使用IE9版本以上或是Google Chrome瀏覽器\n否則可能會造成畫面呈現的問題，謝謝您的配合');
            }
            if (isFirefox) {
        
                alert('您現在使用的瀏覽器為Firefox，請使用IE9版本以上或是Google Chrome瀏覽器\n否則可能會造成畫面呈現的問題，謝謝您的配合');
            }
        /*
            if (isSafari) {

                alert('isSafari or Chrome');
            }
        */
         </script>
    </head>
    <body>
        <div id="map-canvas"></div>
    </body>
</html>