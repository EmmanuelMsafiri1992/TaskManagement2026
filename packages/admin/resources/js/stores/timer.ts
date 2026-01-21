import { appData } from '@/app-data'
import { defineStore } from 'pinia'
import { axios } from 'spack'
import type { TaskI } from 'types'

let timerInterval: ReturnType<typeof setInterval> | undefined

export const useTimerStore = defineStore('timer', {
  state: (): {
    taskId: number | null
    projectId: number | null
    projectName: string | null
    taskTitle: string | null
    processing: boolean
    timers: any
    isTimerRunning: boolean
    timerStartedAt: Date | null
    timerStoppedAt: Date | null
    currentTaskTimer: any
  } => ({
    taskId: null,
    projectId: null,
    projectName: null,
    taskTitle: null,
    processing: false,
    timers: [],
    isTimerRunning: false,
    timerStartedAt: null,
    timerStoppedAt: null,
    currentTaskTimer: {
      h: 0,
      m: 0,
      s: 0,
    },
  }),

  actions: {
    start(task: TaskI) {
      this.stopPrev()

      this.startTimer()
      this.taskId = task.id
      this.taskTitle = task.title
      this.projectId = task.project_id
      this.projectName = task.project.name
      this.processing = true

      axios
        .post('time-logs', {
          taskId: this.taskId,
          projectId: this.projectId,
          start: true,
          currentTime: this.timerStartedAt,
        })
        .then(() => {
          this.processing = false
          this.fetch()
        })
        .catch(() => {
          this.processing = false
        })
    },

    stop() {
      this.stopTimer()

      const taskId = this.taskId
      const projectId = this.projectId
      this.processing = true

      axios
        .post('time-logs', {
          taskId: taskId,
          projectId: projectId,
          stop: true,
          currentTime: this.timerStoppedAt,
        })
        .then(() => {
          this.processing = false
          this.fetch()
        })
        .catch(() => {
          this.processing = false
        })

      this.taskId = null
      this.projectId = null
      this.projectName = null
    },

    stopPrev() {
      if (!this.taskId) return

      console.log('stopPrev')
      this.stopTimer()

      const taskId = this.taskId
      const projectId = this.projectId
      this.processing = true

      axios
        .post('time-logs', {
          taskId: taskId,
          projectId: projectId,
          stop: true,
          currentTime: this.timerStoppedAt,
        })
        .then(({ data }) => {
          console.log(data)
        })
        .catch(() => {
          console.log('error')
          this.processing = false
        })
    },

    fetch() {
      axios.get<any>('time-logs').then((response) => {
        console.log('timer.fetch() response:', response.data)
        this.timers = response.data

        const task = response.data.find(
          (x: any) => x.user.id == appData.user.id,
        )

        console.log('Current user task:', task)

        if (task) {
          this.taskId = task.task.id
          this.taskTitle = task.task.title
          this.projectId = task.task.project_id
          this.projectName = task.task.project.name

          // Calculate elapsed time from started_at_raw
          const startedAt = new Date(task.started_at_raw)
          const now = new Date()
          const elapsedSeconds = Math.floor(
            (now.getTime() - startedAt.getTime()) / 1000,
          )

          console.log('Started at:', startedAt)
          console.log('Elapsed seconds:', elapsedSeconds)
          console.log('Setting timer and starting interval')

          // Set the timer to the elapsed time
          this.setTimer(elapsedSeconds)

          // Start the timer interval
          this.startTimer()
        }
      })
    },

    startTimer() {
      // Clear any existing interval to prevent duplicates
      if (timerInterval) {
        clearInterval(timerInterval)
      }

      timerInterval = setInterval(() => {
        this.currentTaskTimer.s++

        if (this.currentTaskTimer.s >= 60) {
          this.currentTaskTimer.s = 0
          this.currentTaskTimer.m++

          if (this.currentTaskTimer.m >= 60) {
            this.currentTaskTimer.m = 0
            this.currentTaskTimer.h++
          }
        }
      }, 1000)

      this.timerStartedAt = new Date()
      this.isTimerRunning = true
    },

    stopTimer() {
      clearInterval(timerInterval)

      this.timerStoppedAt = new Date()
      this.isTimerRunning = false
    },

    setTimer(seconds: number) {
      // this.isTimerRunning = false

      this.currentTaskTimer.s = Math.floor(seconds % 60)
      this.currentTaskTimer.m = Math.floor((seconds % 3600) / 60)
      // this.currentTaskTimer.h = Math.floor((seconds % (3600 * 24)) / 3600)
      this.currentTaskTimer.h = Math.floor(seconds / 3600)
    },
  },
})
