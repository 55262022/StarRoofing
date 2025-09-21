/**
 * __________________________________________________________________
 *
 * Philippine Address Selector
 * __________________________________________________________________
 *
 * MIT License
 * 
 * Copyright (c) 2020 Wilfred V. Pine
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package Philippine Address Selector
 * @author Wilfred V. Pine <only.master.red@gmail.com>
 * @copyright Copyright 2020 (https://dev.confired.com)
 * @link https://github.com/redmalmon/philippine-address-selector
 * @license https://opensource.org/licenses/MIT MIT License
 */

var my_handlers = {
    fill_provinces: function() {
        // Show loading
        $('#province-loading').show();
        $('#province').prop('disabled', true);
        
        //selected region
        var region_code = $(this).val();
        var region_text = $(this).find("option:selected").text();
        $('#region_name').val(region_text);
        $('#province_name').val('');
        $('#city_name').val('');
        $('#barangay_name').val('');

        //province
        let dropdown = $('#province');
        dropdown.empty();
        dropdown.append('<option selected="true" disabled>Choose Province</option>');
        dropdown.prop('selectedIndex', 0);
        dropdown.prop('disabled', true);

        //city
        let city = $('#city');
        city.empty();
        city.append('<option selected="true" disabled>Select City</option>');
        city.prop('selectedIndex', 0);
        city.prop('disabled', true);

        //barangay
        let barangay = $('#barangay');
        barangay.empty();
        barangay.append('<option selected="true" disabled>Select Barangay</option>');
        barangay.prop('selectedIndex', 0);
        barangay.prop('disabled', true);

        var url = '../ph-json/province.json';
        $.getJSON(url, function(data) {
            var result = data.filter(function(value) {
                return value.region_code == region_code;
            });

            result.sort(function(a, b) {
                return a.province_name.localeCompare(b.province_name);
            });

            $.each(result, function(key, entry) {
                dropdown.append($('<option></option>').attr('value', entry.province_code).text(entry.province_name));
            });
            
            // Hide loading and enable dropdown
            $('#province-loading').hide();
            dropdown.prop('disabled', false);

        }).fail(function() {
            $('#province-loading').hide();
            alert('Error loading provinces. Please try again.');
        });
    },
    fill_cities: function() {
        // Show loading
        $('#city-loading').show();
        $('#city').prop('disabled', true);
        
        var province_code = $(this).val();
        var province_text = $(this).find("option:selected").text();
        $('#province_name').val(province_text);
        $('#city_name').val('');
        $('#barangay_name').val('');

        //city
        let dropdown = $('#city');
        dropdown.empty();
        dropdown.append('<option selected="true" disabled>Choose City/Municipality</option>');
        dropdown.prop('selectedIndex', 0);
        dropdown.prop('disabled', true);

        //barangay
        let barangay = $('#barangay');
        barangay.empty();
        barangay.append('<option selected="true" disabled>Select Barangay</option>');
        barangay.prop('selectedIndex', 0);
        barangay.prop('disabled', true);

        var url = '../ph-json/city.json';
        $.getJSON(url, function(data) {
            var result = data.filter(function(value) {
                return value.province_code == province_code;
            });

            result.sort(function(a, b) {
                return a.city_name.localeCompare(b.city_name);
            });

            $.each(result, function(key, entry) {
                dropdown.append($('<option></option>').attr('value', entry.city_code).text(entry.city_name));
            });
            
            // Hide loading and enable dropdown
            $('#city-loading').hide();
            dropdown.prop('disabled', false);

        }).fail(function() {
            $('#city-loading').hide();
            alert('Error loading cities. Please try again.');
        });
    },
    fill_barangays: function() {
        // Show loading
        $('#barangay-loading').show();
        $('#barangay').prop('disabled', true);
        
        var city_code = $(this).val();
        var city_text = $(this).find("option:selected").text();
        $('#city_name').val(city_text);
        $('#barangay_name').val('');

        // barangay
        let dropdown = $('#barangay');
        dropdown.empty();
        dropdown.append('<option selected="true" disabled>Choose Barangay</option>');
        dropdown.prop('selectedIndex', 0);
        dropdown.prop('disabled', true);

        var url = '../ph-json/barangay.json';
        $.getJSON(url, function(data) {
            var result = data.filter(function(value) {
                return value.city_code == city_code;
            });

            result.sort(function(a, b) {
                return a.brgy_name.localeCompare(b.brgy_name);
            });

            $.each(result, function(key, entry) {
                dropdown.append($('<option></option>').attr('value', entry.brgy_code).text(entry.brgy_name));
            });
            
            // Hide loading and enable dropdown
            $('#barangay-loading').hide();
            dropdown.prop('disabled', false);

        }).fail(function() {
            $('#barangay-loading').hide();
            alert('Error loading barangays. Please try again.');
        });
    },
    onchange_barangay: function() {
        var barangay_text = $(this).find("option:selected").text();
        $('#barangay_name').val(barangay_text);
    }
};

$(function() {
    // Show loading for regions
    $('#region-loading').show();
    
    // Initialize region dropdown
    let dropdown = $('#region');
    dropdown.empty();
    dropdown.append('<option selected="true" disabled>Choose Region</option>');
    dropdown.prop('selectedIndex', 0);
    dropdown.prop('disabled', true);
    
    const url = '../ph-json/region.json';
    $.getJSON(url, function(data) {
        $.each(data, function(key, entry) {
            dropdown.append($('<option></option>').attr('value', entry.region_code).text(entry.region_name));
        });
        
        // Hide loading and enable dropdown
        $('#region-loading').hide();
        dropdown.prop('disabled', false);
        
    }).fail(function() {
        $('#region-loading').hide();
        alert('Error loading regions. Please try again.');
    });

    // Set up event handlers
    $('#region').on('change', my_handlers.fill_provinces);
    $('#province').on('change', my_handlers.fill_cities);
    $('#city').on('change', my_handlers.fill_barangays);
    $('#barangay').on('change', my_handlers.onchange_barangay);
});

// POPULATE ADDRESS
function populateRegions(regionId, provinceId, municipalityId, barangayId, selected = {}) {
    let dropdown = $('#' + regionId);
    dropdown.empty();
    dropdown.append('<option selected="true" disabled>Choose Region</option>');
    dropdown.prop('selectedIndex', 0);

    const url = '../ph-json/region.json';
    $.getJSON(url, function(data) {
        $.each(data, function(key, entry) {
            const isSelected = selected.region_code === entry.region_code ? 'selected' : '';
            dropdown.append(`<option value="${entry.region_code}" ${isSelected}>${entry.region_name}</option>`);
        });

        if (selected.region_code && selected.province_code && selected.city_code && selected.barangay_code) {
            populateProvinces(selected.region_code, provinceId, municipalityId, barangayId, selected);
        }

        dropdown.on('change', function () {
            const region_code = $(this).val();
            populateProvinces(region_code, provinceId, municipalityId, barangayId);

            let name = $(this).find('option:selected').text();
            $('#edit-region-name').val(name);
        });

        if (selected.region_code) {
            dropdown.val(selected.region_code).trigger('change');

            let selectedName = dropdown.find('option:selected').text();
            $('#edit-region-name').val(selectedName);
        }
    });
}

function populateProvinces(region_code, provinceId, municipalityId, barangayId, selected = {}) {
    let dropdown = $('#' + provinceId);
    dropdown.empty();
    dropdown.append('<option selected="true" disabled>Choose State/Province</option>');
    dropdown.prop('selectedIndex', 0);

    const url = '../ph-json/province.json';
    $.getJSON(url, function(data) {
        const result = data.filter(value => value.region_code == region_code);

        $.each(result, function(key, entry) {
            const isSelected = selected.province_code === entry.province_code ? 'selected' : '';
            dropdown.append(`<option value="${entry.province_code}" ${isSelected}>${entry.province_name}</option>`);
        });

        if (selected.province_code && selected.city_code && selected.barangay_code) {
            populateCities(selected.province_code, municipalityId, barangayId, selected);
        }

        dropdown.on('change', function () {
            const province_code = $(this).val();
            populateCities(province_code, municipalityId, barangayId);

            let name = $(this).find('option:selected').text();
            $('#edit-province-name').val(name);
        });

        if (selected.province_code) {
            dropdown.val(selected.province_code).trigger('change');

            let selectedName = dropdown.find('option:selected').text();
            $('#edit-province-name').val(selectedName);
        }
    });
}

function populateCities(province_code, municipalityId, barangayId, selected = {}) {
    let dropdown = $('#' + municipalityId);
    dropdown.empty();
    dropdown.append('<option selected="true" disabled>Choose City/Municipality</option>');
    dropdown.prop('selectedIndex', 0);

    const url = '../ph-json/city.json';
    $.getJSON(url, function(data) {
        const result = data.filter(value => value.province_code == province_code);

        $.each(result, function(key, entry) {
            const isSelected = selected.city_code === entry.city_code ? 'selected' : '';
            dropdown.append(`<option value="${entry.city_code}" ${isSelected}>${entry.city_name}</option>`);
        });

        if (selected.city_code && selected.barangay_code) {
            populateBarangays(selected.city_code, barangayId, selected);
        }

        dropdown.on('change', function () {
            const city_code = $(this).val();
            populateBarangays(city_code, barangayId);

            let name = $(this).find('option:selected').text();
            $('#edit-municipality-name').val(name);
        });

        if (selected.city_code) {
            dropdown.val(selected.city_code).trigger('change');

            let selectedName = dropdown.find('option:selected').text();
            $('#edit-municipality-name').val(selectedName);
        }
    });
}

function populateBarangays(city_code, barangayId, selected = {}) {
    let dropdown = $('#' + barangayId);
    dropdown.empty();
    dropdown.append('<option selected="true" disabled>Choose Barangay</option>');
    dropdown.prop('selectedIndex', 0);

    const url = '../ph-json/barangay.json';
    $.getJSON(url, function(data) {
        const result = data.filter(value => value.city_code == city_code);

        $.each(result, function(key, entry) {
            const isSelected = selected.barangay_code === entry.brgy_code ? 'selected' : '';
            dropdown.append(`<option value="${entry.brgy_code}" ${isSelected}>${entry.brgy_name}</option>`);
        });

        dropdown.on('change', function () {
            const barangay_code = $(this).val();

            let name = $(this).find('option:selected').text();
            $('#edit-barangay-name').val(name);
        });

        if (selected.barangay_code) {
            dropdown.val(selected.barangay_code).trigger('change');

            let selectedName = dropdown.find('option:selected').text();
            $('#edit-barangay-name').val(selectedName);
        }
    });
}