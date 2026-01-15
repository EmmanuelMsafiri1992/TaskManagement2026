<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-semibold text-gray-900">Video Enhancer</h1>
      <p class="mt-1 text-sm text-gray-500">Upload videos to enhance quality, reduce noise, and compress. Max file size: 2GB</p>
    </div>

    <Card>
      <div class="space-y-6 p-6">
        <!-- Upload Section -->
        <div
          class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center"
          :class="{ 'border-indigo-500 bg-indigo-50': isDragging }"
          @dragover.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          @drop.prevent="handleDrop"
        >
          <input
            ref="fileInput"
            type="file"
            accept="video/*"
            class="hidden"
            @change="handleFileSelect"
          />

          <div v-if="!uploading && !selectedFile">
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
              <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="mt-4">
              <button
                type="button"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                @click="($refs.fileInput as HTMLInputElement).click()"
              >
                Select Video
              </button>
            </div>
            <p class="mt-2 text-sm text-gray-500">or drag and drop</p>
            <p class="text-xs text-gray-400 mt-1">MP4, MOV, AVI, WebM up to 2GB</p>
          </div>

          <!-- Upload Progress -->
          <div v-if="uploading" class="space-y-3">
            <p class="text-sm font-medium text-gray-700">Uploading: {{ selectedFile?.name }}</p>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
              <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" :style="{ width: uploadProgress + '%' }"></div>
            </div>
            <p class="text-sm text-gray-500">{{ uploadProgress }}%</p>
          </div>

          <!-- File Selected -->
          <div v-if="selectedFile && !uploading" class="space-y-3">
            <p class="text-sm font-medium text-gray-700">{{ selectedFile.name }}</p>
            <p class="text-sm text-gray-500">{{ formatBytes(selectedFile.size) }}</p>
            <button
              type="button"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
              @click="uploadVideo"
            >
              Upload Video
            </button>
            <button
              type="button"
              class="ml-2 text-sm text-gray-500 hover:text-gray-700"
              @click="selectedFile = null"
            >
              Cancel
            </button>
          </div>
        </div>

        <!-- Enhancement Options (shown after upload) -->
        <div v-if="currentVideo && currentVideo.status === 'pending'" class="border border-gray-200 rounded-lg p-6 space-y-4">
          <h3 class="text-md font-medium text-gray-900">Enhancement Options</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Upscaling -->
            <div class="flex items-start">
              <div class="flex items-center h-5">
                <input
                  id="upscale"
                  v-model="options.upscale"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                >
              </div>
              <div class="ml-3 text-sm">
                <label for="upscale" class="font-medium text-gray-700">Upscale Resolution</label>
                <p class="text-gray-500">Increase video resolution</p>
              </div>
            </div>

            <div v-if="options.upscale">
              <select
                v-model="options.upscale_resolution"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option value="1920:1080">1080p (1920x1080)</option>
                <option value="2560:1440">2K (2560x1440)</option>
                <option value="3840:2160">4K (3840x2160)</option>
              </select>
            </div>

            <!-- Quality Enhancement -->
            <div class="flex items-start">
              <div class="flex items-center h-5">
                <input
                  id="enhance_quality"
                  v-model="options.enhance_quality"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                >
              </div>
              <div class="ml-3 text-sm">
                <label for="enhance_quality" class="font-medium text-gray-700">Enhance Quality</label>
                <p class="text-gray-500">Sharpen and improve video clarity</p>
              </div>
            </div>

            <!-- Noise Reduction -->
            <div class="flex items-start">
              <div class="flex items-center h-5">
                <input
                  id="noise_reduction"
                  v-model="options.noise_reduction"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                >
              </div>
              <div class="ml-3 text-sm">
                <label for="noise_reduction" class="font-medium text-gray-700">Audio Noise Reduction</label>
                <p class="text-gray-500">Remove background noise from audio</p>
              </div>
            </div>

            <!-- Audio Normalization -->
            <div class="flex items-start">
              <div class="flex items-center h-5">
                <input
                  id="normalize_audio"
                  v-model="options.normalize_audio"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                >
              </div>
              <div class="ml-3 text-sm">
                <label for="normalize_audio" class="font-medium text-gray-700">Normalize Audio</label>
                <p class="text-gray-500">Balance audio levels</p>
              </div>
            </div>
          </div>

          <!-- Compression Level -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Compression Level (CRF: {{ options.compression_level }})</label>
            <input
              v-model="options.compression_level"
              type="range"
              min="18"
              max="35"
              class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
            >
            <div class="flex justify-between text-xs text-gray-500">
              <span>Best Quality (larger file)</span>
              <span>Smaller File (lower quality)</span>
            </div>
          </div>

          <!-- Encoding Speed -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Encoding Speed</label>
            <select
              v-model="options.preset"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="ultrafast">Ultra Fast (lowest quality)</option>
              <option value="fast">Fast</option>
              <option value="medium">Medium (recommended)</option>
              <option value="slow">Slow (better quality)</option>
              <option value="veryslow">Very Slow (best quality)</option>
            </select>
          </div>

          <!-- Output Format -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Output Format</label>
            <select
              v-model="options.format"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="mp4">MP4 (H.264)</option>
              <option value="webm">WebM (VP9)</option>
              <option value="mkv">MKV</option>
            </select>
          </div>

          <!-- Target Size -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Target Size (MB) - Optional</label>
            <input
              v-model="options.target_size"
              type="number"
              min="1"
              placeholder="Leave empty for automatic"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
          </div>

          <!-- Size Estimation -->
          <div v-if="estimate" class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-900 mb-2">Size Estimation</h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-gray-500">Original Size</p>
                <p class="font-medium">{{ estimate.original_size_human }}</p>
              </div>
              <div>
                <p class="text-gray-500">Estimated Output</p>
                <p class="font-medium">{{ estimate.estimated_size_human }}</p>
              </div>
              <div>
                <p class="text-gray-500">Compression Ratio</p>
                <p class="font-medium">{{ estimate.compression_ratio }}%</p>
              </div>
              <div>
                <p :class="estimate.feasible ? 'text-green-600' : 'text-red-600'">
                  {{ estimate.feasibility_message }}
                </p>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex space-x-3">
            <button
              type="button"
              :disabled="estimating"
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              @click="getEstimate"
            >
              {{ estimating ? 'Calculating...' : 'Estimate Size' }}
            </button>
            <button
              type="button"
              :disabled="processing"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
              @click="startProcessing"
            >
              {{ processing ? 'Starting...' : 'Start Processing' }}
            </button>
          </div>
        </div>

        <!-- Processing Status -->
        <div v-if="currentVideo && currentVideo.status === 'processing'" class="border border-yellow-200 bg-yellow-50 rounded-lg p-6">
          <div class="flex items-center">
            <svg class="animate-spin h-5 w-5 text-yellow-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <div>
              <p class="font-medium text-yellow-800">Processing: {{ currentVideo.original_filename }}</p>
              <p class="text-sm text-yellow-600">This may take several minutes for large videos...</p>
            </div>
          </div>
        </div>

        <!-- Completed Video -->
        <div v-if="currentVideo && currentVideo.status === 'completed'" class="border border-green-200 bg-green-50 rounded-lg p-6 space-y-4">
          <div class="flex items-center">
            <svg class="h-5 w-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <p class="font-medium text-green-800">Processing Complete!</p>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
              <p class="text-gray-500">Original</p>
              <p class="font-medium">{{ currentVideo.original_size_human }}</p>
            </div>
            <div>
              <p class="text-gray-500">Processed</p>
              <p class="font-medium">{{ currentVideo.processed_size_human }}</p>
            </div>
            <div>
              <p class="text-gray-500">Compression</p>
              <p class="font-medium text-green-600">{{ currentVideo.compression_ratio }}% saved</p>
            </div>
          </div>
          <div class="flex space-x-3">
            <a
              :href="'/api/video-enhancer/' + currentVideo.id + '/download'"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700"
            >
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
              </svg>
              Download
            </a>
            <button
              type="button"
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              @click="confirmDelete(currentVideo.id, false)"
            >
              Keep Original, Delete Processed
            </button>
            <button
              type="button"
              class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50"
              @click="confirmDelete(currentVideo.id, true)"
            >
              Delete All
            </button>
          </div>
        </div>

        <!-- Failed Video -->
        <div v-if="currentVideo && currentVideo.status === 'failed'" class="border border-red-200 bg-red-50 rounded-lg p-6">
          <div class="flex items-center">
            <svg class="h-5 w-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <div>
              <p class="font-medium text-red-800">Processing Failed</p>
              <p class="text-sm text-red-600">{{ currentVideo.error_message }}</p>
            </div>
          </div>
          <button
            type="button"
            class="mt-3 text-sm text-red-600 hover:text-red-800"
            @click="deleteVideo(currentVideo.id)"
          >
            Delete and try again
          </button>
        </div>

        <!-- Previous Videos -->
        <div v-if="videos.length > 0" class="space-y-4">
          <h3 class="text-md font-medium text-gray-900">Previous Videos</h3>
          <div class="space-y-2">
            <div
              v-for="video in videos"
              :key="video.id"
              class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
            >
              <div>
                <p class="font-medium text-gray-900">{{ video.original_filename }}</p>
                <p class="text-sm text-gray-500">
                  {{ video.original_size_human }}
                  <span v-if="video.status === 'completed'" class="text-green-600">
                    &rarr; {{ video.processed_size_human }} ({{ video.compression_ratio }}% saved)
                  </span>
                </p>
              </div>
              <div class="flex items-center space-x-2">
                <span
                  :class="{
                    'bg-yellow-100 text-yellow-800': video.status === 'pending',
                    'bg-blue-100 text-blue-800': video.status === 'processing',
                    'bg-green-100 text-green-800': video.status === 'completed',
                    'bg-red-100 text-red-800': video.status === 'failed'
                  }"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ video.status }}
                </span>
                <button
                  v-if="video.status === 'completed'"
                  class="text-indigo-600 hover:text-indigo-800"
                  @click="downloadVideo(video.id)"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                  </svg>
                </button>
                <button
                  class="text-red-600 hover:text-red-800"
                  @click="confirmDelete(video.id, true)"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { useFlashStore } from 'spack'
import { Card } from 'thetheme'
import axios from 'axios'

interface Video {
  id: number
  original_filename: string
  original_size: number
  original_size_human: string
  processed_size: number | null
  processed_size_human: string
  target_size: number | null
  status: string
  enhancement_options: any
  error_message: string | null
  compression_ratio: number | null
}

interface Estimate {
  original_size: number
  original_size_human: string
  estimated_size: number
  estimated_size_human: string
  compression_ratio: number
  feasible: boolean
  feasibility_message: string
}

const flash = useFlashStore()

const videos = ref<Video[]>([])
const currentVideo = ref<Video | null>(null)
const selectedFile = ref<File | null>(null)
const uploading = ref(false)
const uploadProgress = ref(0)
const isDragging = ref(false)
const estimating = ref(false)
const processing = ref(false)
const estimate = ref<Estimate | null>(null)
const statusInterval = ref<number | null>(null)

const options = ref({
  upscale: false,
  upscale_resolution: '1920:1080',
  enhance_quality: false,
  noise_reduction: true,
  normalize_audio: true,
  compression_level: 23,
  preset: 'medium',
  format: 'mp4',
  target_size: null as number | null
})

onMounted(() => {
  loadVideos()
})

onUnmounted(() => {
  if (statusInterval.value) {
    clearInterval(statusInterval.value)
  }
})

const loadVideos = () => {
  axios.get('/api/video-enhancer').then((response: any) => {
    videos.value = response.data.data
    const active = videos.value.find(v => v.status === 'pending' || v.status === 'processing')
    if (active) {
      currentVideo.value = active
      if (active.status === 'processing') {
        startStatusPolling()
      }
    }
  }).catch(() => {
    flash.error('Failed to load videos')
  })
}

const handleDrop = (e: DragEvent) => {
  isDragging.value = false
  const files = e.dataTransfer?.files
  if (files && files.length > 0) {
    selectedFile.value = files[0]
  }
}

const handleFileSelect = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    selectedFile.value = target.files[0]
  }
}

const formatBytes = (bytes: number): string => {
  const units = ['B', 'KB', 'MB', 'GB']
  let size = bytes
  let unitIndex = 0
  while (size >= 1024 && unitIndex < units.length - 1) {
    size /= 1024
    unitIndex++
  }
  return `${size.toFixed(2)} ${units[unitIndex]}`
}

const uploadVideo = () => {
  if (!selectedFile.value) return

  uploading.value = true
  uploadProgress.value = 0

  const formData = new FormData()
  formData.append('video', selectedFile.value)

  axios.post('/api/video-enhancer/upload', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
    onUploadProgress: (progressEvent: any) => {
      uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total)
    }
  }).then((response: any) => {
    flash.success('Video uploaded successfully')
    currentVideo.value = response.data.data
    selectedFile.value = null
    loadVideos()
  }).catch((error: any) => {
    flash.error(error.response?.data?.message || 'Upload failed')
  }).finally(() => {
    uploading.value = false
    uploadProgress.value = 0
  })
}

const getEstimate = () => {
  if (!currentVideo.value) return

  estimating.value = true
  axios.post(`/api/video-enhancer/${currentVideo.value.id}/estimate`, options.value)
    .then((response: any) => {
      estimate.value = response.data.data
    })
    .catch((error: any) => {
      flash.error(error.response?.data?.message || 'Failed to estimate')
    })
    .finally(() => {
      estimating.value = false
    })
}

const startProcessing = () => {
  if (!currentVideo.value) return

  processing.value = true
  axios.post(`/api/video-enhancer/${currentVideo.value.id}/process`, options.value)
    .then(() => {
      flash.success('Processing started')
      if (currentVideo.value) {
        currentVideo.value.status = 'processing'
      }
      startStatusPolling()
    })
    .catch((error: any) => {
      flash.error(error.response?.data?.message || 'Failed to start processing')
    })
    .finally(() => {
      processing.value = false
    })
}

const startStatusPolling = () => {
  if (statusInterval.value) {
    clearInterval(statusInterval.value)
  }

  statusInterval.value = window.setInterval(() => {
    if (!currentVideo.value) return

    axios.get(`/api/video-enhancer/${currentVideo.value.id}/status`)
      .then((response: any) => {
        const status = response.data.data
        if (currentVideo.value) {
          currentVideo.value.status = status.status
          currentVideo.value.processed_size = status.processed_size
          currentVideo.value.processed_size_human = status.processed_size_human
          currentVideo.value.compression_ratio = status.compression_ratio
          currentVideo.value.error_message = status.error_message
        }

        if (status.status === 'completed' || status.status === 'failed') {
          if (statusInterval.value) {
            clearInterval(statusInterval.value)
            statusInterval.value = null
          }
          loadVideos()
          if (status.status === 'completed') {
            flash.success('Video processing completed!')
          }
        }
      })
      .catch(() => {
        // Ignore polling errors
      })
  }, 3000)
}

const downloadVideo = (id: number) => {
  window.location.href = `/api/video-enhancer/${id}/download`
}

const confirmDelete = (id: number, deleteAll: boolean) => {
  const message = deleteAll
    ? 'Delete both original and processed video?'
    : 'Delete only the processed video? You can re-process later.'

  if (confirm(message)) {
    if (deleteAll) {
      deleteVideo(id)
    } else {
      deleteProcessedVideo(id)
    }
  }
}

const deleteVideo = (id: number) => {
  axios.delete(`/api/video-enhancer/${id}`)
    .then(() => {
      flash.success('Video deleted')
      if (currentVideo.value?.id === id) {
        currentVideo.value = null
      }
      loadVideos()
    })
    .catch((error: any) => {
      flash.error(error.response?.data?.message || 'Failed to delete')
    })
}

const deleteProcessedVideo = (id: number) => {
  axios.delete(`/api/video-enhancer/${id}/processed`)
    .then(() => {
      flash.success('Processed video deleted')
      loadVideos()
    })
    .catch((error: any) => {
      flash.error(error.response?.data?.message || 'Failed to delete')
    })
}
</script>
