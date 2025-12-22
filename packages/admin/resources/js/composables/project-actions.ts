import { axios } from 'spack'

export const projectAction = {
  archive() {
    console.log('archive')
  },

  favorite(id: number) {
    axios.patch(`/projects/${id}/favorite`).then(() => {
      // useToastStore().flash(data.message)
      // useFavoriteStore().items = data.favorites.favorites
      // isFavoriteProject.value = data.favorite
    })
  },

  edit() {
    console.log('edit!')
  },
}
