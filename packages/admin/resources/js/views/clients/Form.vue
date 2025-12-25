<template>
  <div>
    <div class="border-b border-gray-200 px-6 py-4">
      <h2 class="text-lg font-semibold text-gray-900">
        {{ modelValue ? __('Edit Client') : __('Add Client') }}
      </h2>
    </div>

    <div class="space-y-6 px-6 py-4">
      <!-- Basic Information -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-gray-700">
            {{ __('Name') }} <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': errors.name }"
            :placeholder="__('Client name')"
            required
          />
          <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
          <input
            v-model="form.email"
            type="email"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': errors.email }"
            :placeholder="__('email@example.com')"
          />
          <p v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Phone') }}</label>
          <input
            v-model="form.phone"
            type="tel"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :placeholder="__('Primary phone number')"
          />
          <p v-if="errors.phone" class="mt-2 text-sm text-red-600">{{ errors.phone[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Secondary Phone') }}</label>
          <input
            v-model="form.secondary_phone"
            type="tel"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :placeholder="__('Alternative phone number')"
          />
        </div>
      </div>

      <!-- Company Information -->
      <div class="border-t border-gray-200 pt-6">
        <h3 class="mb-4 text-md font-medium text-gray-900">{{ __('Company Information') }}</h3>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Company Name') }}</label>
            <input
              v-model="form.company_name"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :placeholder="__('Company or organization name')"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Business Type') }}</label>
            <input
              v-model="form.business_type"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :placeholder="__('e.g., Retail, Technology, Healthcare')"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Website') }}</label>
            <input
              v-model="form.website"
              type="url"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :class="{ 'border-red-300': errors.website }"
              placeholder="https://example.com"
            />
            <p v-if="errors.website" class="mt-2 text-sm text-red-600">{{ errors.website[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">
              {{ __('Status') }} <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.status"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              required
            >
              <option value="prospect">{{ __('Prospect') }}</option>
              <option value="active">{{ __('Active') }}</option>
              <option value="inactive">{{ __('Inactive') }}</option>
            </select>
            <p v-if="errors.status" class="mt-2 text-sm text-red-600">{{ errors.status[0] }}</p>
          </div>
        </div>
      </div>

      <!-- Address Information -->
      <div class="border-t border-gray-200 pt-6">
        <h3 class="mb-4 text-md font-medium text-gray-900">{{ __('Address') }}</h3>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">{{ __('Address') }}</label>
            <textarea
              v-model="form.address"
              rows="2"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :placeholder="__('Street address')"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Country') }}</label>
            <select
              v-model="form.country"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">{{ __('Select Country') }}</option>
              <option v-for="country in countries" :key="country" :value="country">
                {{ country }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('City') }}</label>
            <!-- Show dropdown if cities are available for selected country -->
            <select
              v-if="availableCities.length > 0"
              v-model="form.city"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">{{ __('Select City') }}</option>
              <option v-for="city in availableCities" :key="city" :value="city">
                {{ city }}
              </option>
            </select>
            <!-- Show text input if no cities defined for the country -->
            <input
              v-else
              v-model="form.city"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :placeholder="form.country ? __('Enter city name') : __('Select country first')"
            />
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div class="border-t border-gray-200 pt-6">
        <label class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
        <textarea
          v-model="form.notes"
          rows="3"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :placeholder="__('Additional notes about this client')"
        ></textarea>
      </div>
    </div>

    <div class="flex justify-end gap-3 bg-gray-50 px-6 py-4">
      <TheButton variant="secondary" @click="$emit('close')">
        {{ __('Cancel') }}
      </TheButton>
      <TheButton :disabled="processing" @click="submit">
        {{ modelValue ? __('Update Client') : __('Create Client') }}
      </TheButton>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { axios } from 'spack/axios'
import TheButton from '@/thetheme/components/TheButton.vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const processing = ref(false)
const errors = ref({})

const countries = [
  'Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Australia', 'Austria',
  'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bhutan',
  'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Brazil', 'Brunei', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon',
  'Canada', 'Cape Verde', 'Central African Republic', 'Chad', 'Chile', 'China', 'Colombia', 'Comoros', 'Congo', 'Costa Rica',
  'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Democratic Republic of the Congo', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'East Timor',
  'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Eswatini', 'Ethiopia', 'Fiji', 'Finland',
  'France', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Greece', 'Grenada', 'Guatemala', 'Guinea',
  'Guinea-Bissau', 'Guyana', 'Haiti', 'Honduras', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq',
  'Ireland', 'Israel', 'Italy', 'Ivory Coast', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati',
  'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania',
  'Luxembourg', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Mauritania', 'Mauritius',
  'Mexico', 'Micronesia', 'Moldova', 'Monaco', 'Mongolia', 'Montenegro', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia',
  'Nauru', 'Nepal', 'Netherlands', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'North Korea', 'North Macedonia', 'Norway',
  'Oman', 'Pakistan', 'Palau', 'Palestine', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Poland',
  'Portugal', 'Qatar', 'Romania', 'Russia', 'Rwanda', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino',
  'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands',
  'Somalia', 'South Africa', 'South Korea', 'South Sudan', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Sweden', 'Switzerland',
  'Syria', 'Taiwan', 'Tajikistan', 'Tanzania', 'Thailand', 'Togo', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey',
  'Turkmenistan', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay', 'Uzbekistan', 'Vanuatu',
  'Vatican City', 'Venezuela', 'Vietnam', 'Yemen', 'Zambia', 'Zimbabwe'
]

// Cities by country - comprehensive list for Malawi, major cities for other countries
const citiesByCountry = {
  'Malawi': [
    'Lilongwe', 'Blantyre', 'Mzuzu', 'Zomba', 'Kasungu', 'Mangochi', 'Karonga', 'Salima', 'Nkhotakota', 'Liwonde',
    'Nsanje', 'Rumphi', 'Dedza', 'Ntcheu', 'Balaka', 'Mchinji', 'Mulanje', 'Thyolo', 'Chiradzulu', 'Phalombe',
    'Chitipa', 'Nkhata Bay', 'Likoma', 'Ntchisi', 'Dowa', 'Mwanza', 'Neno', 'Chikwawa', 'Machinga', 'Monkey Bay',
    'Cape Maclear', 'Chipoka', 'Dwangwa', 'Ekwendeni', 'Jenda', 'Luchenza', 'Lunzu', 'Migowi', 'Mponela', 'Mzimba',
    'Nkaya', 'Songwe', 'Thekerani', 'Chilumba', 'Embangweni', 'Livingstonia', 'Makanjira', 'Malomo', 'Namitete'
  ],
  'Zambia': [
    'Lusaka', 'Kitwe', 'Ndola', 'Kabwe', 'Chingola', 'Mufulira', 'Livingstone', 'Luanshya', 'Kasama', 'Chipata',
    'Kalulushi', 'Mazabuka', 'Mongu', 'Solwezi', 'Choma', 'Kafue', 'Mansa', 'Mpika', 'Petauke', 'Senanga'
  ],
  'Tanzania': [
    'Dar es Salaam', 'Mwanza', 'Arusha', 'Dodoma', 'Mbeya', 'Morogoro', 'Tanga', 'Zanzibar City', 'Kigoma', 'Moshi',
    'Tabora', 'Songea', 'Musoma', 'Shinyanga', 'Iringa', 'Sumbawanga', 'Mtwara', 'Lindi', 'Bukoba', 'Singida'
  ],
  'Mozambique': [
    'Maputo', 'Beira', 'Nampula', 'Chimoio', 'Nacala', 'Quelimane', 'Tete', 'Lichinga', 'Pemba', 'Xai-Xai',
    'Maxixe', 'Inhambane', 'Cuamba', 'Montepuez', 'Chokwe', 'Dondo', 'Angoche', 'Mocuba', 'Gurué', 'Matola'
  ],
  'Zimbabwe': [
    'Harare', 'Bulawayo', 'Chitungwiza', 'Mutare', 'Gweru', 'Kwekwe', 'Kadoma', 'Masvingo', 'Chinhoyi', 'Marondera',
    'Norton', 'Chegutu', 'Bindura', 'Beitbridge', 'Victoria Falls', 'Hwange', 'Kariba', 'Rusape', 'Chiredzi', 'Zvishavane'
  ],
  'South Africa': [
    'Johannesburg', 'Cape Town', 'Durban', 'Pretoria', 'Port Elizabeth', 'Bloemfontein', 'East London', 'Polokwane',
    'Nelspruit', 'Kimberley', 'Rustenburg', 'Pietermaritzburg', 'Vanderbijlpark', 'Soweto', 'Benoni', 'Tembisa',
    'Welkom', 'Midrand', 'Sandton', 'Centurion'
  ],
  'Kenya': [
    'Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret', 'Thika', 'Malindi', 'Kitale', 'Garissa', 'Nyeri',
    'Machakos', 'Meru', 'Lamu', 'Naivasha', 'Embu', 'Kakamega', 'Kericho', 'Bungoma', 'Migori', 'Kilifi'
  ],
  'Uganda': [
    'Kampala', 'Gulu', 'Lira', 'Mbarara', 'Jinja', 'Mbale', 'Mukono', 'Masaka', 'Entebbe', 'Kasese',
    'Hoima', 'Arua', 'Fort Portal', 'Soroti', 'Kabale', 'Tororo', 'Iganga', 'Mityana', 'Mubende', 'Masindi'
  ],
  'Botswana': [
    'Gaborone', 'Francistown', 'Molepolole', 'Serowe', 'Selibe Phikwe', 'Maun', 'Kanye', 'Mahalapye', 'Mogoditshane',
    'Mochudi', 'Lobatse', 'Palapye', 'Ramotswa', 'Thamaga', 'Jwaneng', 'Letlhakane', 'Orapa', 'Kasane', 'Nata', 'Ghanzi'
  ],
  'United Kingdom': [
    'London', 'Birmingham', 'Manchester', 'Glasgow', 'Liverpool', 'Leeds', 'Sheffield', 'Edinburgh', 'Bristol',
    'Leicester', 'Coventry', 'Bradford', 'Cardiff', 'Belfast', 'Nottingham', 'Newcastle', 'Southampton', 'Brighton', 'Plymouth', 'Oxford'
  ],
  'United States': [
    'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas',
    'San Jose', 'Austin', 'Jacksonville', 'Fort Worth', 'Columbus', 'Charlotte', 'San Francisco', 'Indianapolis',
    'Seattle', 'Denver', 'Washington D.C.', 'Boston', 'Nashville', 'Detroit', 'Portland', 'Las Vegas', 'Miami', 'Atlanta'
  ],
  'China': [
    'Beijing', 'Shanghai', 'Guangzhou', 'Shenzhen', 'Chengdu', 'Hangzhou', 'Wuhan', 'Xian', 'Nanjing', 'Tianjin',
    'Suzhou', 'Chongqing', 'Qingdao', 'Dalian', 'Xiamen', 'Ningbo', 'Zhengzhou', 'Changsha', 'Dongguan', 'Shenyang'
  ],
  'India': [
    'Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Chennai', 'Kolkata', 'Ahmedabad', 'Pune', 'Surat', 'Jaipur',
    'Lucknow', 'Kanpur', 'Nagpur', 'Patna', 'Indore', 'Thane', 'Bhopal', 'Visakhapatnam', 'Vadodara', 'Coimbatore'
  ],
  'Nigeria': [
    'Lagos', 'Kano', 'Ibadan', 'Abuja', 'Port Harcourt', 'Benin City', 'Maiduguri', 'Zaria', 'Aba', 'Jos',
    'Ilorin', 'Oyo', 'Enugu', 'Abeokuta', 'Sokoto', 'Onitsha', 'Warri', 'Calabar', 'Uyo', 'Kaduna'
  ],
  'Ghana': [
    'Accra', 'Kumasi', 'Tamale', 'Takoradi', 'Ashaiman', 'Tema', 'Cape Coast', 'Sekondi', 'Obuasi', 'Teshie',
    'Koforidua', 'Sunyani', 'Ho', 'Wa', 'Bolgatanga', 'Techiman', 'Nungua', 'Madina', 'Kasoa', 'Aflao'
  ],
  'Ethiopia': [
    'Addis Ababa', 'Dire Dawa', 'Mekelle', 'Gondar', 'Hawassa', 'Bahir Dar', 'Adama', 'Jimma', 'Dessie', 'Jijiga',
    'Harar', 'Shashamane', 'Debre Birhan', 'Arba Minch', 'Hosaena', 'Dilla', 'Nekemte', 'Asella', 'Debre Markos', 'Kombolcha'
  ],
  'Rwanda': [
    'Kigali', 'Butare', 'Gitarama', 'Ruhengeri', 'Gisenyi', 'Byumba', 'Cyangugu', 'Nyanza', 'Kibungo', 'Kibuye'
  ],
  'Burundi': [
    'Bujumbura', 'Gitega', 'Muyinga', 'Ruyigi', 'Ngozi', 'Rutana', 'Bururi', 'Makamba', 'Kayanza', 'Muramvya'
  ],
  'Democratic Republic of the Congo': [
    'Kinshasa', 'Lubumbashi', 'Mbuji-Mayi', 'Kananga', 'Kisangani', 'Bukavu', 'Tshikapa', 'Kolwezi', 'Likasi', 'Goma'
  ]
}

// Computed property for available cities based on selected country
const availableCities = computed(() => {
  if (form.country && citiesByCountry[form.country]) {
    return [...citiesByCountry[form.country]].sort()
  }
  return []
})

// Watch for country changes to reset city if not in new country's list
watch(() => form.country, (newCountry, oldCountry) => {
  if (oldCountry && newCountry !== oldCountry) {
    // Check if current city exists in new country's cities
    const newCities = citiesByCountry[newCountry] || []
    if (!newCities.includes(form.city)) {
      form.city = ''
    }
  }
})

const form = reactive({
  name: '',
  email: '',
  phone: '',
  secondary_phone: '',
  company_name: '',
  business_type: '',
  address: '',
  city: '',
  country: '',
  website: '',
  notes: '',
  status: 'prospect'
})

const submit = async () => {
  processing.value = true
  errors.value = {}

  const url = props.modelValue
    ? `clients/${props.modelValue.id}`
    : 'clients'

  try {
    if (props.modelValue) {
      await axios.put(url, form)
    } else {
      await axios.post(url, form)
    }
    emit('saved')
    emit('close')
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }
    console.error('Failed to save client:', error)
  } finally {
    processing.value = false
  }
}

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    Object.assign(form, {
      name: newVal.name || '',
      email: newVal.email || '',
      phone: newVal.phone || '',
      secondary_phone: newVal.secondary_phone || '',
      company_name: newVal.company_name || '',
      business_type: newVal.business_type || '',
      address: newVal.address || '',
      city: newVal.city || '',
      country: newVal.country || '',
      website: newVal.website || '',
      notes: newVal.notes || '',
      status: newVal.status || 'prospect'
    })
  } else {
    Object.assign(form, {
      name: '',
      email: '',
      phone: '',
      secondary_phone: '',
      company_name: '',
      business_type: '',
      address: '',
      city: '',
      country: '',
      website: '',
      notes: '',
      status: 'prospect'
    })
  }
}, { immediate: true })
</script>
