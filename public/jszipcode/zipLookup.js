/*
 * $.zipLookup v0.1
 *   - by Ari Asulin (ari.asulin at gmail.com)
 *   - jQuery plugin to dynamically fill in City/State Form Fields using an ajax Zipcode lookup
 *   - Apache License, Version 2.0
 *
 */

(function($) {
    $.extend({
        zipLookupSetup: function( settings ) {
            jQuery.extend( jQuery.ajaxSettings, settings );
        },

        zipLookupSettings: {
            zipField: null,
            cityField: null,
            stateField: null,
            libPath: 'jszipcode/',
            country: 'us',
            onLookup: function () {},
            onNotFound: function () {},
            onError: function () {}
        },

        zipLookup: function( zipVal, s, onNotFound) {
            if(s instanceof Function)
            {
                s = {onLookup : s};
            }
            s = jQuery.extend(true, {}, jQuery.zipLookupSettings, s);
            if(onNotFound instanceof Function)
                s.onNotFound = onNotFound;

            zipVal = parseInt(zipVal);
            if(!zipVal)
                throw "Invalid zipVal: "+ zipField.val();
            var zipGroup = parseInt(zipVal / 100);
            var zipSet = parseInt(zipVal % 100);

            var path = s.libPath + s.country + "/" + zipGroup + ".json";

            $.ajax({
                url: path,
                dataType: 'json',
                success: function (data) {
                    if(data === undefined || data[0] === undefined)
                        return s.onNotFound();
                    var cityID = data[0][zipSet];
                    if(data[1][cityID] === undefined)
                        return s.onNotFound();
                    var cityData = data[1][cityID].split('|');
                    var cityName = cityData[0];
                    if(!cityData[1]) cityData[1] = 0;
                    var stateID = cityData[1];
                    var stateData = data[2][stateID].split('|');
                    var stateName = stateData[1];
                    var stateShortName = stateData[0];
                    s.onLookup(cityName, stateName, stateShortName);
                },
                fail: function (jqXHR, textStatus){
                    s.onError(jqXHR, textStatus);
                }
            });

        }

    });
})(jQuery);