<template>
  <div v-if="contractDaysRemaining !== null && appData.employment_type === 'Contract'">
    <div
      class="contract-countdown-badge"
      :class="{
        'contract-urgent': contractDaysRemaining <= 30,
        'contract-warning': contractDaysRemaining > 30 && contractDaysRemaining <= 90,
        'contract-normal': contractDaysRemaining > 90
      }"
      @click="showModal = true"
    >
      <div class="flex items-center space-x-2">
        <svg
          class="h-5 w-5"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
          />
        </svg>
        <div class="flex flex-col leading-tight">
          <span class="text-xs font-medium opacity-90">Contract Ends In</span>
          <span class="text-lg font-bold">
            {{ contractDaysRemaining }}
            <span class="text-sm">{{ contractDaysRemaining === 1 ? 'day' : 'days' }}</span>
          </span>
        </div>
      </div>

      <!-- Tooltip on hover -->
      <div class="contract-tooltip">
        <div class="text-xs font-semibold">Click for Details</div>
        <div class="mt-1 text-xs">
          <div>End Date: {{ formatDate(appData.user.contract_end_date) }}</div>
          <div v-if="appData.user.contract_type" class="mt-0.5">
            Type: {{ formatContractType(appData.user.contract_type) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="contract-modal-overlay"
      @click="showModal = false"
    >
      <div class="contract-modal" @click.stop>
        <div class="contract-modal-header">
          <h3 class="text-xl font-bold text-gray-900">Contract Information</h3>
          <button
            class="close-button"
            @click="showModal = false"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="contract-modal-body">
          <!-- Status Badge -->
          <div class="status-badge" :class="getStatusClass()">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-bold">Status: {{ getContractStatus() }}</span>
          </div>

          <!-- Contract Details Grid -->
          <div class="details-grid">
            <div class="detail-item">
              <div class="detail-label">Contract Type</div>
              <div class="detail-value">{{ formatContractType(appData.user.contract_type) }}</div>
            </div>

            <div class="detail-item">
              <div class="detail-label">End Date</div>
              <div class="detail-value">{{ formatDate(appData.user.contract_end_date) }}</div>
            </div>

            <div class="detail-item">
              <div class="detail-label">Days Remaining</div>
              <div class="detail-value countdown-value">
                {{ contractDaysRemaining }} {{ contractDaysRemaining === 1 ? 'day' : 'days' }}
              </div>
            </div>

            <div class="detail-item">
              <div class="detail-label">Renewal Status</div>
              <div class="detail-value renewal-status">
                <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>Non-renewable</span>
              </div>
            </div>
          </div>

          <!-- Urgency Message -->
          <div v-if="contractDaysRemaining <= 30" class="urgency-message">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
              <div class="font-bold">Urgent: Contract Expiring Soon</div>
              <div class="text-sm">Please contact HR if you need assistance regarding your contract.</div>
            </div>
          </div>
        </div>

        <div class="contract-modal-footer">
          <button
            class="modal-button"
            @click="showModal = false"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed, ref } from 'vue'
  import { appData } from '../../app-data'

  const contractDaysRemaining = computed(() => appData.contract_days_remaining)
  const showModal = ref(false)

  function formatDate(date: string): string {
    if (!date) return '-'
    const d = new Date(date)
    return d.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    })
  }

  function formatContractType(type: string): string {
    if (!type) return 'Fixed Term'
    const types = {
      'fixed-term': 'Fixed Term',
      'permanent': 'Permanent',
      'freelance': 'Freelance',
    }
    return types[type] || type
  }

  function getContractStatus(): string {
    if (!contractDaysRemaining.value) return 'Unknown'
    if (contractDaysRemaining.value <= 30) return 'Expiring Soon'
    if (contractDaysRemaining.value <= 90) return 'Active (Reminder)'
    return 'Active'
  }

  function getStatusClass(): string {
    if (!contractDaysRemaining.value) return ''
    if (contractDaysRemaining.value <= 30) return 'status-urgent'
    if (contractDaysRemaining.value <= 90) return 'status-warning'
    return 'status-normal'
  }
</script>

<style scoped>
  .contract-countdown-badge {
    position: relative;
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 0.75rem;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .contract-countdown-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  /* Urgent: 30 days or less - Red/Orange */
  .contract-urgent {
    background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
    color: white;
    animation: pulse-urgent 2s ease-in-out infinite;
  }

  @keyframes pulse-urgent {
    0%, 100% {
      box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3),
                  0 0 0 0 rgba(239, 68, 68, 0.4);
    }
    50% {
      box-shadow: 0 4px 16px rgba(239, 68, 68, 0.4),
                  0 0 0 8px rgba(239, 68, 68, 0);
    }
  }

  /* Warning: 31-90 days - Yellow/Amber */
  .contract-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #eab308 100%);
    color: white;
  }

  /* Normal: 90+ days - Blue */
  .contract-normal {
    background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
    color: white;
  }

  /* Tooltip */
  .contract-tooltip {
    position: absolute;
    bottom: calc(100% + 10px);
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.9);
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
    z-index: 1000;
  }

  .contract-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: rgba(0, 0, 0, 0.9);
  }

  .contract-countdown-badge:hover .contract-tooltip {
    opacity: 1;
  }

  /* Modal Overlay */
  .contract-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 1rem;
  }

  /* Modal Container */
  .contract-modal {
    background: white;
    border-radius: 1rem;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease-out;
  }

  @keyframes modalSlideIn {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Modal Header */
  .contract-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
  }

  .close-button {
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: all 0.2s;
    color: #6b7280;
  }

  .close-button:hover {
    background: #f3f4f6;
    color: #111827;
  }

  /* Modal Body */
  .contract-modal-body {
    padding: 1.5rem;
  }

  /* Status Badge */
  .status-badge {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
    font-size: 1.125rem;
  }

  .status-urgent {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    color: #dc2626;
    border: 2px solid #fca5a5;
  }

  .status-warning {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    color: #d97706;
    border: 2px solid #fcd34d;
  }

  .status-normal {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    color: #2563eb;
    border: 2px solid #93c5fd;
  }

  /* Details Grid */
  .details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
  }

  .detail-item {
    padding: 1rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
  }

  .detail-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
  }

  .detail-value {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
  }

  .countdown-value {
    font-size: 1.5rem;
    color: #dc2626;
  }

  .renewal-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  /* Urgency Message */
  .urgency-message {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    border: 2px solid #fca5a5;
    border-radius: 0.75rem;
    color: #dc2626;
  }

  .urgency-message svg {
    flex-shrink: 0;
  }

  /* Modal Footer */
  .contract-modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
  }

  .modal-button {
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
    color: white;
    border-radius: 0.5rem;
    font-weight: 600;
    transition: all 0.3s;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
  }

  .modal-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
  }

  /* Responsive */
  @media (max-width: 768px) {
    .contract-countdown-badge {
      padding: 0.4rem 0.75rem;
    }

    .contract-countdown-badge .text-lg {
      font-size: 1rem;
    }

    .contract-countdown-badge .text-xs {
      font-size: 0.65rem;
    }

    .details-grid {
      grid-template-columns: 1fr;
    }

    .contract-modal {
      margin: 0.5rem;
    }
  }
</style>
