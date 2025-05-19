import { setupAuthGuard } from 'src/router/guards'

export default async ({ router }) => {
  await setupAuthGuard(router)
} 