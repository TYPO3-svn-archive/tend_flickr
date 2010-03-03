# By Oto Brglez - <otobrglez@tend.si>
# Powered by ORG. TEND d.o.o. - www.tend.si

####################################################################################################
# How to display Photostream from user
plugin.tx_tendflickr_pi1 {
    flickr {
        api_key = 2460a66b65f2d13340c9b0f1b975c550
        api_cache = 120
    }

    show {
        display = Photostream
        params {
        	user_id = restFlickr_People_findByUsername(username=Oto Brglez)["user"]["nsid"]
        }
    }

    smarty {
    	debugging = false
    }
}

####################################################################################################
# Generic API call
plugin.tx_tendflickr_pi1 {
    flickr {
        api_key = 2460a66b65f2d13340c9b0f1b975c550
        api_cache = 0
    }

    show {
        authenticate = 1
        call = restFlickr_Photosets_getList
        display = generic

        params {
        	user_id = restFlickr_People_findByUsername(username=Oto Brglez)["user"]["nsid"]
        }
    }

    smarty {
    	debugging = false
    }
}