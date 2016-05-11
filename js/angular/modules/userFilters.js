var userFilters = angular.module('userFilters', []);

var SNAKE_CASE_REGEXP = /[A-Z]/g;
function snake_case(name, separator){
    separator = separator || '_';
    return name.replace(SNAKE_CASE_REGEXP, function(letter, pos) {
        return (pos ? separator : '') + letter.toLowerCase();
    });
}

userFilters.filter('currency', function() {
    return function(input) {
        if(!input) return '$00.00';

        if(typeof input == 'number') input = input.toString();

        input = input.replace(/^0|,|\$/g, '');
        var new_input = '',
            number_parts = input.split('.'),
            prepend_count = number_parts[0].length%3;


        if(!prepend_count) {
            var whole_part_segments = number_parts[0].match(/.{3}/g);
        } else {
            new_input += number_parts[0].slice(0,prepend_count);

            var remainder = number_parts[0].slice(prepend_count),
                whole_part_segments = remainder.match(/.{3}/g);
        }

        if(new_input) new_input += whole_part_segments == null ? '.' : ',';
        angular.forEach(whole_part_segments, function(part, i) {
            new_input += part + (i == whole_part_segments.length-1 ? '.'  : ',');
        });

        if(!number_parts[1]) {
            new_input += '00';
        } else if(number_parts[1].length == 1) {
            new_input += number_parts[1]+'0';
        } else if(number_parts[1].length > 2) {
            new_input += number_parts[1].slice(0,2);
        } else {
            new_input += number_parts[1];
        }

        return '$'+new_input;
    };
});

userFilters.filter('snakeCase', function() {
    return function(input) {
        return snake_case(input.replace(/\s/g, ''), '-');;
    }
});

userFilters.filter('campaignViewReadable', function() {
    return function(input) {

        var readable = 'All',
            readables = {
                'pending' : 'Pending',
                'active' : 'Active',
                'complete' : 'Completed'
            };

        if(readables.hasOwnProperty(input)) readable = readables[input];

        return readable;
    }
});

userFilters.filter('percentage', function() {
    return function(input) {
        if(!input) input = 0;
        input = input*100;
        if(typeof input == 'number') input = input.toString();

        input = parseFloat(input).toFixed(2);

        return input.slice(-1) == '%' ? input : input + '%';
    }
});

userFilters.filter('prependA', function() {
    return function(input) {
        return /^[aeiou]/.test(input) ? 'an '+ input : 'a ' + input;
    }
});

/* --- Inline CSS Filters --- */
userFilters.filter('widthCss', function() {
    return function(input) {
        if(input.slice(-1) != '%' && !/px|em/i.test(input.slice(-2))) input += 'px';
        return input ? "width: "+input+";" : "";
    }
});

userFilters.filter('backgroundImageCss', function() {
    return function(input) {
        return input ? "background-image: url("+input+");" : "";
    }
});