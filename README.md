# fuzzChecker
Small PHP script based on [Bo0oM's fuzz list](https://github.com/Bo0oM/fuzz.txt)

Can be used both from CLI and from browser (both GET and POST).

Input options:
 - **target** - required, an URL to check for accessibility
 - **verbose** - optional, by default, the script returns just a list of unsafe URLs, this options adds some flavor text
 - **explicit** - optional, only available if **verbose** is on, adds all the safe URLs to the output
 
 All optional fields are "enable-flags" - option is enabled if a corresponding flag is present in the request.
